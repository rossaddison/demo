<?php

declare(strict_types=1);

use App\Asset\AppAsset;
use App\User\User;
use App\Widget\PerformanceMetrics;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Button;
use Yiisoft\Html\Tag\Form;
use Yiisoft\Html\Tag\Label;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use Yiisoft\Yii\Bootstrap5\Dropdown;
use Yiisoft\Yii\Bootstrap5\DropdownItem;
use Yiisoft\Yii\Bootstrap5\DropdownToggleVariant;
use Yiisoft\Yii\Bootstrap5\Nav;
use Yiisoft\Yii\Bootstrap5\NavBar;
use Yiisoft\Yii\Bootstrap5\NavBarExpand;
use Yiisoft\Yii\Bootstrap5\NavBarPlacement;
use Yiisoft\Yii\Bootstrap5\NavLink;
use Yiisoft\Yii\Bootstrap5\NavStyle;

/**
 * @var UrlGeneratorInterface $urlGenerator
 * @var CurrentRoute          $currentRoute
 * @var WebView               $this
 * @var AssetManager          $assetManager
 * @var TranslatorInterface   $translator
 * @var string                $content
 *
 * @see \App\ApplicationViewInjection
 *
 * @var User|null $user
 * @var string    $csrf
 * @var string    $brandLabel
 */
$assetManager->register(AppAsset::class);

$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$currentRouteName = $currentRoute->getName() ?? '';
$isGuest = $user === null || $user->getId() === null;

$this->beginPage();
?>
    <!DOCTYPE html>
    <html class="h-100" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Yii Demo<?= $this->getTitle() ? ' - ' . Html::encode($this->getTitle()) : '' ?></title>
        <?php $this->head() ?>
    </head>
    <body class="cover-container-fluid d-flex w-100 h-100 mx-auto flex-column">
    <header class="mb-auto">
        <?php $this->beginBody() ?>

        <?= NavBar::widget()
            ->addAttributes([])    
            ->addClass('navbar navbar-light bg-light navbar-expand-sm text-white')     
            ->brandText($brandLabel)
            ->brandUrl($urlGenerator->generate('site/index'))
            ->class()
            ->container(false) 
            ->containerAttributes([])      
            ->expand(NavBarExpand::LG)
            ->id('navbar')      
            //->innerContainerAttributes(['class' => 'container-md'])      
            ->placement(NavBarPlacement::STICKY_TOP)     
            ->begin(); ?>

        <?= Nav::widget()
            ->currentPath($currentRoute
                ->getUri()
                ->getPath())
            ->addClass('navbar-nav mx-auto')
            ->items(
                 NavLink::to(
                    Label::tag()
                    ->attributes(['class' => 'bi bi-door-open-fill text-success'])
                    ->content($translator->translate('menu.blog')),    
                    $urlGenerator->generate('blog/index'), 
                    StringHelper::startsWith(
                            $currentRouteName,
                            'blog/'
                        ) && $currentRouteName !== 'blog/comment/index', 
                    !StringHelper::startsWith(
                            $currentRouteName,
                            'blog/'
                        ) && $currentRouteName == 'blog/comment/index',                        
                    false
                ),
                NavLink::to(
                    Label::tag()
                    ->content($translator->translate('menu.comments-feed')),    
                    $urlGenerator->generate('blog/comment/index'), 
                    StringHelper::startsWith($currentRouteName, 'user/'), 
                    false,                        
                    false
                ),    
                NavLink::to(
                    Label::tag()
                    ->content($translator->translate('menu.contact')),    
                    $urlGenerator->generate('site/contact'), 
                    false, 
                    false,                        
                    false
                ),    
                NavLink::to(
                    Label::tag()
                    ->content($translator->translate('menu.swagger')),    
                    $urlGenerator->generate('swagger/index'), 
                    false, 
                    false,                        
                    false
                )
            ) ?>

        <?= Nav::widget()
            ->currentPath($currentRoute
                ->getUri()
                ->getPath())
            ->class('navbar-nav')
            ->items(
                Dropdown::widget()
                ->addClass('bi bi-translate')  
                ->addAttributes([
                    'style' => 'font-size: 1rem; color: cornflowerblue;',
                    'title' => $translator->translate('menu.language'),
                    'url' => '#'
                ])
                ->toggleVariant(DropdownToggleVariant::INFO)
                ->toggleContent('')        
                ->toggleSizeSmall(true)        
                ->items(
                    DropdownItem::link('English', $urlGenerator->generateFromCurrent(['_language' => 'en'], fallbackRouteName: 'site/index')),
                    DropdownItem::link('German / Deutsch', $urlGenerator->generateFromCurrent(['_language' => 'de'], fallbackRouteName: 'site/index')),
                    DropdownItem::link('Indonesian / bahasa Indonesia', $urlGenerator->generateFromCurrent(['_language' => 'id'], fallbackRouteName: 'site/index')),
                    DropdownItem::link('Russian / Русский', $urlGenerator->generateFromCurrent(['_language' => 'ru'], fallbackRouteName: 'site/index')),
                    DropdownItem::link('Slovakian / Slovenský', $urlGenerator->generateFromCurrent(['_language' => 'sk'], fallbackRouteName: 'site/index')),
                ),
                NavLink::to(
                    Label::tag()
                    ->content($isGuest ? $translator->translate('menu.login') : ''),    
                    $urlGenerator->generate('auth/login'), 
                    false, 
                    !$isGuest,                        
                    false
                ),
                NavLink::to(
                    Label::tag()
                    ->content($isGuest ? $translator->translate('menu.signup') : ''),    
                    $urlGenerator->generate('auth/signup'), 
                    false, 
                    !$isGuest,                        
                    false
                ),
                NavLink::to(
                    Label::tag()
                    ->content(!$isGuest ? $translator->translate('layout.change-password') : ''),    
                    $urlGenerator->generate('auth/change'), 
                    false, 
                    // Only make visible i.e not disabled if NOT a guest i.e. authenticated and/or authorized
                    // disabled => is a Guest    
                    $isGuest,                        
                    false
                )
            )
            ->styles(NavStyle::NAVBAR); ?>
        
        <?= 
            $isGuest ? '' : Form::tag()
                    ->post($urlGenerator->generate('auth/logout'))
                    ->csrf($csrf)
                    ->open()
                . '<div class="mb-1">'
                . Button::submit(
                    $translator->translate('menu.logout', ['login' => Html::encode($user->getLogin())])
                )
                    ->class('btn btn-primary')
                . '</div>'
                . Form::tag()->close()          
        ?>
        <?= NavBar::end(); ?>
    </header>

    <main class="container py-3">
        <?= $content ?>
    </main>

    <footer class='mt-auto bg-dark py-3'>
        <div class = 'd-flex flex-fill align-items-center container-fluid'>
            <div class = 'd-flex flex-fill float-start'>
                <i class=''></i>
                <a class='text-decoration-none' href='https://www.yiiframework.com/' target='_blank' rel='noopener'>
                    Yii Framework - <?= date('Y') ?> -
                </a>
                <div class="ms-2 text-white">
                    <?= PerformanceMetrics::widget() ?>
                </div>
            </div>

            <div class='float-end'>
                <a class='text-decoration-none px-1' href='https://github.com/yiisoft' target='_blank' rel='noopener' >
                    <i class="bi bi-github text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://join.slack.com/t/yii/shared_invite/enQtMzQ4MDExMDcyNTk2LTc0NDQ2ZTZhNjkzZDgwYjE4YjZlNGQxZjFmZDBjZTU3NjViMDE4ZTMxNDRkZjVlNmM1ZTA1ODVmZGUwY2U3NDA' target='_blank' rel='noopener'>
                    <i class="bi bi-slack text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://www.facebook.com/groups/yiitalk' target='_blank' rel='noopener'>
                    <i class="bi bi-facebook text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://twitter.com/yiiframework' target='_blank' rel='noopener'>
                    <i class="bi bi-twitter text-white"></i>
                </a>
                <a class='text-decoration-none px-1' href='https://t.me/yii3ru' target='_blank' rel='noopener'>
                    <i class="bi bi-telegram text-white"></i>
                </a>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
    </body>
    </html>
<?php
$this->endPage();
