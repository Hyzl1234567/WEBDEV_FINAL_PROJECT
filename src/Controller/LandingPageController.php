<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LandingPageController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home()
    {
        return $this->render('landingPage/home.html.twig');
    }

    #[Route('/login', name: 'login')]
    public function login()
    {
        return $this->render('landingPage/login.html.twig');
    }

    #[Route('/about', name: 'about')]
    public function about()
    {
        return $this->render('landingPage/about.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact()
    {
        return $this->render('landingPage/contact.html.twig');
    }

    #[Route('/admin', name: 'admin')]
    public function admin()
    {
        return $this->render('landingPage/admin.html.twig');
    }

     #[Route('/register', name: 'register')]
    public function register()
    {
        return $this->render('registration/register.html.twig');
    }
}