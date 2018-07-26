<?php
namespace GameX\Core\Mail;

class Message {

	const CHARSET = "UTF-8";

	const CRLF = "\r\n";

	/**
	 * header multipart boundaryMixed
	 */
	protected $boundaryMixed = null;

	/**
	 * header multipart alternative
	 */
	protected $boundaryAlternative = null;

    /**
     * @var Email
     */
    protected $from;

	/**
	 * @var string
	 */
	protected $subject = '';

	/**
	 * @var Email
	 */
	protected $replyTo = null;

    /**
     * @var Email[]
     */
    protected $to = [];

    /**
     * @var Email[]
     */
    protected $cc = [];

    /**
     * @var Email[]
     */
    protected $bcc = [];

    /**
     * mail body
     */
    protected $body = '';

    /**
     *mail attachment
     */
    protected $attachment = [];

	/**
	 * @param Email $from
	 */
    public function __construct(Email $from) {
    	$this->from = $from;
	}

	/**
	 * @return Email
	 */
    public function getFrom() {
    	return $this->from;
	}

	/**
	 * set mail subject
	 * @param string $subject
	 * @return $this
	 */
	public function setSubject($subject) {
		$this->subject = (string) $subject;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSubject() {
		return !empty($this->subject) ? sprintf('=?utf-8?B?%s?= ', base64_encode($this->subject)) : '';
	}

	/**
	 * @param Email $replyTo
	 * @return $this
	 */
	public function setReplyTo(Email $replyTo) {
		$this->replyTo = $replyTo;
		return $this;
	}

	public function getReplyTo() {
		return $this->replyTo;
	}

	/**
     * @param Email $email
     * @return $this
     */
    public function addTo(Email $email) {
        $this->to[] = $email;
        return $this;
    }

	/**
	 * @return Email[]
	 */
	public function getTo() {
		return $this->to;
	}

    /**
     * @param Email $email
     * @return $this
     */
    public function addCc(Email $email) {
        $this->cc[] = $email;
        return $this;
    }

	/**
	 * @return Email[]
	 */
	public function getCc() {
		return $this->cc;
	}

    /**
     * @param Email $email
     * @return $this
     */
    public function addBcc(Email $email) {
        $this->bcc[] = $email;
        return $this;
    }

	/**
	 * @return Email[]
	 */
	public function getBcc() {
		return $this->bcc;
	}

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body) {
        $this->body = (string) $body;
        return $this;
    }

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->body;
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
	 * @return string
	 */
	public function getBoundaryMixed() {
		if ($this->boundaryMixed === null) {
			$this->boundaryMixed = md5(md5(time() . 'MMailer') . uniqid());
		}
		return $this->boundaryMixed;
	}

	/**
	 * @return string
	 */
	public function getBoundaryAlternative() {
		if ($this->boundaryAlternative === null) {
			$this->boundaryAlternative = md5(md5(time() . 'AMailer') . uniqid());
		}
		return $this->boundaryAlternative;
	}

	/**
	 * @return array
	 */
	public function getHeaders() {
		$headers = [];
		$headers['Date'] = date('r');

		$headers['Return-Path'] = $this->from->getEmail();

		$tmp = [];
		foreach ($this->to as $email) {
			$tmp[] = (string) $email;
		}
		$headers['To'] = implode(', ', $tmp);

		$tmp = [];
		foreach ($this->cc as $email) {
			$tmp[] = (string) $email;
		}
		$headers['Cc'] = implode(', ', $tmp);

		$tmp = [];
		foreach ($this->bcc as $email) {
			$tmp[] = (string) $email;
		}
		$headers['Bcc'] = implode(', ', $tmp);

		if ($this->replyTo !== null) {
			$headers['Reply-To'] = (string) $this->replyTo;
		}

		$headers['Message-ID'] = '<' . md5(uniqid()) . '@' . $this->from->getEmail() . '>';
		$headers['X-Priority'] = '3';
		$headers['MIME-Version'] = '1.0';
		if ($this->hasAttachment()) {
			$headers['Content-Type'] = "multipart/mixed; \r\n\tboundary=\"" . $this->getBoundaryMixed() . "\"";
		}
		return $headers;
	}

	public function getMessage() {
		return $this->hasAttachment() ? $this->createBodyWithAttachment() : $this->createBody();
	}

	/**
	 * @return string
	 */
	protected function createBody() {
		$body = chunk_split(base64_encode($this->body));
		$result = '';
		$result .= 'Content-Type: multipart/alternative; boundary="' . $this->getBoundaryAlternative(). '"' . self::CRLF . self::CRLF;
		$result .= '--' . $this->getBoundaryAlternative() . self::CRLF;
		$result .= 'Content-Type: text/plain; charset="' . self::CHARSET . '"' . self::CRLF;
		$result .= "Content-Transfer-Encoding: base64" . self::CRLF . self::CRLF;
		$result .= $body . self::CRLF . self::CRLF;
		$result .= '--' . $this->getBoundaryAlternative() . self::CRLF;
		$result .= 'Content-Type: text/html; charset="' . self::CHARSET . '"' . self::CRLF;
		$result .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
		$result .= $body . self::CRLF . self::CRLF;
		$result .= '--' . $this->getBoundaryAlternative() . '--' . self::CRLF;
		return $result;
	}

	/**
	 * @return string
	 */
	protected function createBodyWithAttachment() {
		$body = chunk_split(base64_encode($this->body));
		$result = self::CRLF . self::CRLF;
		$result .= '--' . $this->getBoundaryMixed() . self::CRLF;
		$result .= 'Content-Type: multipart/alternative; boundary="' . $this->getBoundaryAlternative() . '"' . self::CRLF . self::CRLF;
		$result .= '--' . $this->getBoundaryAlternative() . self::CRLF;
		$result .= 'Content-Type: text/plain; charset="' . self::CHARSET . '"' . self::CRLF;
		$result .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
		$result .= $body . self::CRLF . self::CRLF;
		$result .= "--" . $this->getBoundaryAlternative() . self::CRLF;
		$result .= 'Content-Type: text/html; charset="' . self::CHARSET . '"' . self::CRLF . self::CRLF;
		$result .= $body . self::CRLF . self::CRLF;
		$result .= '--' . $this->getBoundaryAlternative() . '--' . self::CRLF;
		foreach ($this->attachment as $name => $path) {
			if (!is_readable($path)) {
				continue;
			}
			$result .= self::CRLF;
			$result .= '--' . $this->getBoundaryMixed() . self::CRLF;
			$result .= 'Content-Type: ' . mime_content_type($path) . '; name="' . $name . '"' . self::CRLF;
			$result .= 'Content-Transfer-Encoding: base64' . self::CRLF;
			$result .= 'Content-Disposition: attachment; filename="' . $name . '"' . self::CRLF . self::CRLF;
			$result .= chunk_split(base64_encode(file_get_contents($path))) . self::CRLF;
		}
		$result .= self::CRLF . self::CRLF;
		$result .= '--' . $this->getBoundaryMixed() . '--' . self::CRLF;
		return $result;
	}
}
