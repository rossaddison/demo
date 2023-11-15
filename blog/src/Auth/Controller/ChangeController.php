<?php

declare(strict_types=1);

namespace App\Auth\Controller;

use App\Auth\AuthService;
use App\Auth\Identity;
use App\Auth\IdentityRepository;
use App\Auth\Form\ChangeForm;
use App\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Form\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Translator\TranslatorInterface as Translator;
use Yiisoft\User\CurrentUser;
use Yiisoft\Yii\View\ViewRenderer;

final class ChangeController
{
    public function __construct(
      private Translator $translator,
      private CurrentUser $current_user,
      private WebControllerService $webService, 
      private ViewRenderer $viewRenderer,
    )
    {
      $this->current_user = $current_user;
      $this->translator = $translator;
      $this->viewRenderer = $viewRenderer->withControllerName('change');
    }
    
    public function change(
      AuthService $authService,
      Identity $identity,
      IdentityRepository $identityRepository,
      ServerRequestInterface $request,
      FormHydrator $formHydrator,
      ChangeForm $changeForm
    ): ResponseInterface {
      // permit an authenticated user with permission editPost (ie. not a guest) only and null!== current user
      if (!$authService->isGuest()) {
        // see demo/blog/resources/rbac  
        if ($this->current_user->can('editPost',[])) {
          // readonly the login detail on the change form
          $identity_id = $this->current_user->getIdentity()->getId();
          if (null!==$identity_id) {
            $identity = $identityRepository->findIdentity($identity_id);
            if (null!==$identity) {
              // Identity and User are in a HasOne relationship so no null value
              $login = $identity->getUser()?->getLogin();
              if ($request->getMethod() === Method::POST
                && $formHydrator->populate($changeForm, $request->getParsedBody())
                && $changeForm->change() 
              ) {
                // Identity implements CookieLoginIdentityInterface: ensure the regeneration of the cookie auth key by means of $authService->logout();
                // @see vendor\yiisoft\user\src\Login\Cookie\CookieLoginIdentityInterface 

                // Specific note: "Make sure to invalidate earlier issued keys when you implement force user logout,
                // PASSWORD CHANGE and other scenarios, that require forceful access revocation for old sessions.
                // The authService logout function will regenerate the auth key here => overwriting any auth key
                $authService->logout();
                return $this->redirectToMain();
              }
              return $this->viewRenderer->render('change', ['formModel' => $changeForm, 'login' => $login]);
            } // identity
          } // identity_id 
        } // current user
      } // auth service  
      return $this->redirectToMain();
    } // reset
        
    private function redirectToMain(): ResponseInterface
    {
      return $this->webService->getRedirectResponse('site/index');
    }
}
