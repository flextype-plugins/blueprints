<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Flextype\Middlewares\CsrfMiddleware;
use Flextype\Plugin\Acl\Middlewares\AclIsUserLoggedInMiddleware;
use Flextype\Plugin\Acl\Middlewares\AclIsUserLoggedInRolesInMiddleware;

flextype()->group('/' . $admin_route, function () : void {

    flextype()->get('/blueprint', function(Request $request, Response $response) {
        flextype('registry')->set('workspace', ['icon' => ['name' => 'newspaper', 'set' => 'bootstrap']]);

        return flextype('twig')->render(
            $response,
            'plugins/blueprints/blocks/ui.html',
            []
        );
    });

});/*->add(new AclIsUserLoggedInMiddleware(['redirect' => 'admin.accounts.login']))
  ->add(new AclIsUserLoggedInRolesInMiddleware(['redirect' => (flextype('acl')->isUserLoggedIn() ? 'admin.accounts.no-access' : 'admin.accounts.login'),
                                                'roles' => 'admin']))
  ->add(new CsrfMiddleware());*/
