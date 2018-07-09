<?php

namespace GameX\Core\Mail;


class Message{
    /**
     * from name
     */
    protected $fromName;

    /**
     * from email
     */
    protected $fromEmail;

    /**
     * to email
     */
    protected $to = array();

    /**
     * cc email
     */
    protected $cc = array();

    /**
     * bcc email
     */
    protected $bcc = array();

    /**
     * mail subject
     */
    protected $subject;

    /**
     * mail body
     */
    protected $body;

    /**
     *mail attachment
     */
    protected $attachment = [];

    /**
     * Address for the reply-to header
     * @var string
     */
    protected $replyToName;

    /**
     * Address for the reply-to header
     * @var string
     */
    protected $replyToEmail;

	/**
	 * header multipart boundaryMixed
	 * @var string
	 */
	protected $boundaryMixed;

	/**
	 * header multipart alternative
	 * @var string
	 */
	protected $boundaryAlternative;

    /**
     * @param string$name
     * @param string $email
     * @return $this
     */
    public function setReplyTo($name, $email) {
        $this->replyToName = $name;
        $this->replyToEmail = $email;
        return $this;
    }

    /**
     * set mail from
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function setFrom($name, $email) {
        $this->fromName = $name;
        $this->fromEmail = $email;
        return $this;
    }

    /**
     * add mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addTo($name, $email) {
        $this->to[$email] = $name;
        return $this;
    }

    /**
     * add cc mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addCc($name, $email) {
        $this->cc[$email] = $name;
        return $this;
    }

    /**
     * add bcc mail receiver
     * @param string $name
     * @param string $email
     * @return $this
     */
    public function addBcc($name, $email) {
        $this->bcc[$email] = $name;
        return $this;
    }

    /**
     * set mail subject
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    /**
     * set mail body
     * @param string $body
     * @return $this
     */
    public function setBody($body) {
        $this->body = $body;
        return $this;
    }

    /**
     * add mail attachment
     * @param $name
     * @param $path
     * @return $this
     */
    public function addAttachment($name, $path) {
        $this->attachment[$name] = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName() {
        return $this->fromName;
    }

    /**
     * @return string
     */
    public function getFromEmail() {
        return $this->fromEmail;
    }

    /**
     * @return string
     */
    public function getReplyName() {
        return $this->replyToName;
    }

    /**
     * @return string
     */
    public function getReplyEmail() {
        return $this->replyToEmail;
    }

    /**
     * @return mixed
     */
    public function getTo() {
        return $this->to;
    }

    /**
     * @return mixed
     */
    public function getCc() {
        return $this->cc;
    }

    /**
     * @return mixed
     */
    public function getBcc() {
        return $this->bcc;
    }

    /**
     * @return mixed
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @return mixed
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getAttachment() {
        return $this->attachment;
    }

    /**
     * @return bool
     */
    public function hasAttachment() {
        return count($this->attachment) > 0;
    }

	/**
	 * @param string $boundaryMixed
	 * @return $this
	 */
    public function setBoundaryMixed($boundaryMixed) {
    	$this->boundaryMixed = $boundaryMixed;
    	return $this;
	}

	/**
	 * @param string $boundaryAlternative
	 * @return $this
	 */
	public function setBoundaryAlternative($boundaryAlternative) {
    	$this->boundaryAlternative = $boundaryAlternative;
    	return $this;
	}

	/**
	 * @return string
	 */
	public function getBoundaryMixed() {
		return $this->boundaryMixed ;
	}

	/**
	 * @return string
	 */
	public function getBoundaryAlternative() {
		return $this->boundaryAlternative;
	}
}
