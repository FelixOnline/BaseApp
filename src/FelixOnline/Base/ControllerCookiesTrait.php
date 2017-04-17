<?php
namespace FelixOnline\Base;

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

    // Remove from response, not remove from browser
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
        \Psr\Http\Message\ResponseInterface $response,
        $domain = null,
        $path = null
    ) {
        $setCookie = \Dflydev\FigCookies\SetCookie::createExpired($cookie);

        if($domain) {
            $setCookie = $setCookie->withDomain($domain);
        }

        if($path) {
            $setCookie = $setCookie->withPath($path);
        }

        return $this->setCookie($setCookie, $response);
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
