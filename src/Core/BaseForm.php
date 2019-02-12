<?php

namespace GameX\Core;

use \Psr\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \GameX\Core\Helpers\UriHelper;
use \GameX\Core\Lang\Language;
use \GameX\Core\Forms\Form;
use \GameX\Core\Validate\Validator;
use \GameX\Core\Exceptions\ValidationException;

abstract class BaseForm
{
    
    /**
     * @var ContainerInterface
     */
    protected static $container;
    
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var Form
     */
    protected $form;
    
    /**
     * @var Language
     */
    protected $translate;
    
    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }
    
    /**
     * @return bool
     */
    public function getIsSubmitted()
    {
        return $this->form->getIsSubmitted();
    }
    
    
    public function getIsValid()
    {
        return $this->form->getIsValid();
    }
    
    /**
     * @return $this
     */
    public function create()
    {
        $this->form = new Form(static::$container->get('session'), static::$container->get('lang'), $this->name);
        $this->createForm();
        
        /** @var ServerRequestInterface $request */
        $request = static::$container->get('request');
        $this->form->setAction(UriHelper::getUrl($request->getUri()));
        return $this;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @return boolean
     * @throws ValidationException
     */
    public function process(ServerRequestInterface $request)
    {
        $this->form->processRequest($request);
        
        if (!$this->form->getIsSubmitted()) {
            return null;
        }
        
        if (!$this->form->getIsValid()) {
            throw new ValidationException();
        }
        
        return $this->processForm();
    }
    
    public function getForm()
    {
        return $this->form;
    }
    
    /**
     * @param $section
     * @param $key
     * @param array $args
     * @return string
     */
    protected function getTranslate($section, $key, ...$args)
    {
        if ($this->translate === null) {
            $this->translate = static::$container->get('lang');
        }
        return $this->translate->format($section, $key, $args);
    }
    
    /**
     * @noreturn
     */
    protected function createForm()
    {
        $this->makeForm($this->form);
        $this->makeValidator($this->form->getValidator());
    }
    
    /**
     * @param Form $form
     */
    protected function makeForm(Form $form) {}
    
    /**
     * @param Validator $validator
     * @throws \Exception
     */
    protected function makeValidator(Validator $validator) {}
    
    /**
     * @return boolean
     */
    abstract protected function processForm();
}
