<?php

namespace App\Security;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedHandler implements AccessDeniedHandlerInterface{
    public function __construct(SessionInterFace $session, RouterInterface $router)
    {
        $this->session = $session;
        $this->router = $router;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $this->session->getFlashBag()->add('Warning', "Please sign in ADMIN account for this action!");

        return new RedirectResponse($this->router->generate("app_login"));
    }
}

?>