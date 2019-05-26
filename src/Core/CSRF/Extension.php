<?php
namespace GameX\Core\CSRF;

use \Twig_Extension;
use \Twig_SimpleFunction;

class Extension extends Twig_Extension {
    /**
     * @var Token
     */
    protected $token;

    /**
     * Extension constructor.
     * @param Token $token
     */
    public function __construct(Token $token) {
        $this->token = $token;
    }

    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'csrf_token',
                [$this, 'renderCSRFToken'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'csrf_token_json',
                [$this, 'renderCSRFTokenJSON'],
                ['is_safe' => ['html']]
            )
        ];
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    public function renderCSRFToken() {
        return sprintf(
            '<input type="hidden" name="%s" value="%s"><input type="hidden" name="%s" value="%s">',
            $this->token->getNameKey(),
            $this->token->getName(),
            $this->token->getTokenKey(),
            $this->token->getToken()
        );
    }
    
    /**
     * @return false|string
     * @throws \Exception
     */
    public function renderCSRFTokenJSON() {
        return [
            $this->token->getNameKey() => $this->token->getName(),
            $this->token->getTokenKey() => $this->token->getToken()
        ];
    }
}
