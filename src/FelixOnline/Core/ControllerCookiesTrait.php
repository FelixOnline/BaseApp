<?php
namespace FelixOnline\Core;

trait ControllerCookiesTrait {
    private function getCookie(
        $cookie,
        \Psr\Http\Message\ServerRequestInterface $request
    ) {
        return \Dflydev\FigCookies\FigRequestCookies::get($request, $cookie);
    }

    private function setCookie(
        \DFlydev\FigCookies\SetCookie $cookie,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        return \Dflydev\FigCookies\FigResponseCookies::set(
            $response,
            $cookie
        );
    }

    private function removeCookie(
        $cookie,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        return \Dflydev\FigCookies\FigResponseCookies::remove(
            $response,
            $cookie
        );
    }

    private function expireCookie(
        $cookie,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        return \Dflydev\FigCookies\FigResponseCookies::expire(
            $response,
            $cookie
        );
    }

    private function modifyCookie(
        \DFlydev\FigCookies\SetCookie $cookie,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        return \Dflydev\FigCookies\FigResponseCookies::modify(
            $response,
            $cookie->getName(),
            function($cookie) { return $cookie; }
        );
    }
}
