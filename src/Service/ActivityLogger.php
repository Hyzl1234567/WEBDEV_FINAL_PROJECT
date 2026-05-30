<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use App\Repository\ActivityLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityLogger
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private ActivityLogRepository $activityLogRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RequestStack           $requestStack,
        ActivityLogRepository  $activityLogRepository
    ) {
        $this->entityManager         = $entityManager;
        $this->requestStack          = $requestStack;
        $this->activityLogRepository = $activityLogRepository;
    }

    public function log(
        ?User   $user,
        string  $action,
        ?string $entity      = null,
        ?int    $entityId    = null,
        ?string $description = null
    ): void {
        // Skip logging for plain ROLE_USER accounts (no admin/staff/customer role)
        if ($user && $this->getUserRole($user) === 'ROLE_USER') {
            return;
        }

        $log = new ActivityLog();
        $log->setUser($user);
        $log->setAction($action);
        $log->setEntity($entity);
        $log->setEntityId($entityId);
        $log->setDescription($description);
        $log->setCreatedAt(new \DateTimeImmutable());

        if ($user) {
            $log->setUsername($user->getUsername());
            $log->setRole($this->getUserRole($user));
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $log->setIpAddress($request->getClientIp());
        }

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    // ─────────────────────────────────────────────
    // AUTH
    // ─────────────────────────────────────────────

    public function logLogin(User $user): void
    {
        // Deduplicate: skip if this user already has a LOGIN within the last 5 minutes
        if ($this->activityLogRepository->findRecentLoginForUser($user, 300)) {
            return;
        }

        $this->log(
            $user,
            'LOGIN',
            'User',
            $user->getId(),
            sprintf(
                '%s "%s" logged in successfully.',
                $this->getUserRoleLabel($user),
                $user->getUsername()
            )
        );
    }

    public function logLogout(User $user): void
    {
        $this->log(
            $user,
            'LOGOUT',
            'User',
            $user->getId(),
            sprintf(
                '%s "%s" logged out.',
                $this->getUserRoleLabel($user),
                $user->getUsername()
            )
        );
    }

    // ─────────────────────────────────────────────
    // CRUD — accept optional $snapshot (ignored in
    // description but keeps controller calls valid)
    // ─────────────────────────────────────────────

    public function logCreate(User $user, string $entity, int $entityId, string $entityName, array $snapshot = []): void
    {
        $this->log(
            $user,
            'CREATE',
            $entity,
            $entityId,
            sprintf(
                '%s "%s" created a new %s: "%s" (ID: %d).',
                $this->getUserRoleLabel($user),
                $user->getUsername(),
                $entity,
                $this->extractName($entityName),
                $entityId
            )
        );
    }

    public function logUpdate(User $user, string $entity, int $entityId, string $entityName, array $snapshot = []): void
    {
        // Build a context-aware description using snapshot data if available
        $description = $this->buildUpdateDescription($user, $entity, $entityId, $entityName, $snapshot);

        $this->log(
            $user,
            'UPDATE',
            $entity,
            $entityId,
            $description
        );
    }

    public function logOrderPlaced(User $user, int $orderId, string $customerName, string $productName, int $quantity, float $totalPrice): void
    {
        $this->log(
            $user,
            'ORDER_PLACED',
            'Order',
            $orderId,
            sprintf(
                '%s "%s" placed Order #%d — Customer: "%s", Product: "%s" ×%d (Total: ₱%.2f).',
                $this->getUserRoleLabel($user),
                $user->getUsername(),
                $orderId,
                $customerName,
                $productName,
                $quantity,
                $totalPrice
            )
        );
    }

    public function logDelete(User $user, string $entity, int $entityId, string $entityName, array $snapshot = []): void
    {
        $this->log(
            $user,
            'DELETE',
            $entity,
            $entityId,
            sprintf(
                '%s "%s" permanently deleted %s: "%s" (ID: %d).',
                $this->getUserRoleLabel($user),
                $user->getUsername(),
                $entity,
                $this->extractName($entityName),
                $entityId
            )
        );
    }

    // ─────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────

    /**
     * Builds a smart description for UPDATE actions by inspecting
     * the snapshot to understand what specifically changed.
     */
    private function buildUpdateDescription(User $user, string $entity, int $entityId, string $entityName, array $snapshot): string
    {
        $actor = sprintf('%s "%s"', $this->getUserRoleLabel($user), $user->getUsername());
        $target = $this->extractName($entityName);

        // Password reset
        if (isset($snapshot['note']) && $snapshot['note'] === 'password reset by admin') {
            return sprintf(
                '%s reset the password for %s "%s" (ID: %d).',
                $actor,
                $entity,
                $snapshot['username'] ?? $target,
                $entityId
            );
        }

        // Status toggle / archive
        if (isset($snapshot['old_status'], $snapshot['new_status'])) {
            return sprintf(
                '%s changed status of %s "%s" (ID: %d) from "%s" to "%s".',
                $actor,
                $entity,
                $snapshot['username'] ?? $target,
                $entityId,
                $snapshot['old_status'],
                $snapshot['new_status']
            );
        }

        // Role change detected
        if (isset($snapshot['role'])) {
            return sprintf(
                '%s updated %s "%s" (ID: %d). Previous role was "%s".',
                $actor,
                $entity,
                $snapshot['username'] ?? $target,
                $entityId,
                $snapshot['role']
            );
        }

        // Generic update fallback
        return sprintf(
            '%s updated %s "%s" (ID: %d).',
            $actor,
            $entity,
            $target,
            $entityId
        );
    }

    /**
     * Strips the "User: " prefix from entityName strings like
     * "User: staff05 (ID: 9)" to get just "staff05 (ID: 9)".
     */
    private function extractName(string $entityName): string
    {
        // Strip common prefixes like "User: ", "Product: ", etc.
        return preg_replace('/^[A-Za-z]+:\s*/', '', $entityName);
    }

    private function getUserRole(User $user): string
    {
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN',    $roles)) return 'ROLE_ADMIN';
        if (in_array('ROLE_STAFF',    $roles)) return 'ROLE_STAFF';
        if (in_array('ROLE_CUSTOMER', $roles)) return 'ROLE_CUSTOMER';
        return 'ROLE_USER';
    }

    private function getUserRoleLabel(User $user): string
    {
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN',    $roles)) return 'Admin';
        if (in_array('ROLE_STAFF',    $roles)) return 'Staff';
        if (in_array('ROLE_CUSTOMER', $roles)) return 'Customer';
        return 'User';
    }
}
