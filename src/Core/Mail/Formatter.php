<?php
namespace GameX\Core\Mail;

class Formatter {
    const CHARSET = "UTF-8";

    const CRLF = "\r\n";

    /**
     * header multipart boundaryMixed
     */
    protected $boundaryMixed;

    /**
     * header multipart alternative
     */
    protected $boundaryAlternative;

	/**
	 * @param Message $message
	 * @return string
	 */
    public function getFromMail(Message $message) {
    	return $this->formatEmail($message->getFromEmail(), $message->getFromName());
	}

	/**
	 * @param Message $message
	 * @return string
	 */
	public function getSubject(Message $message) {
		$subject = $message->getSubject();
		return !empty($subject) ? $this->encode($subject) : '';
	}

    /**
     * Create headers
	 * @param Message $message
     * @return array
     */
    public function getHeaders(Message $message) {
        $headers = [];
        $headers['Date'] = date('r');

        $headers['Return-Path'] = $message->getFromEmail();
//        $headers['From'] = $this->formatEmail($message->getFromEmail(), $message->getFromName());

        $tmp = [];
        foreach ($message->getTo() as $email => $name) {
            $tmp[] = $this->formatEmail($email, $name);
        }
        $headers['To'] = implode(', ', $tmp);

        $tmp = [];
        foreach ($message->getCc() as $email => $name) {
            $tmp[] = $this->formatEmail($email, $name);
        }
        $headers['Cc'] = implode(', ', $tmp);

        $tmp = [];
        foreach ($message->getBcc() as $email => $name) {
            $tmp[] = $this->formatEmail($email, $name);
        }
        $headers['Bcc'] = implode(', ', $tmp);

        $headers['Reply-To'] = $this->formatEmail($message->getReplyEmail(), $message->getReplyName());

//        $subject = $message->getSubject();
//        $headers['Subject'] = !empty($subject) ? $this->encode($subject) : '';

        $headers['Message-ID'] = '<' . md5(uniqid()) . '@' . $message->getFromEmail() . '>';
        $headers['X-Priority'] = '3';
        $headers['MIME-Version'] = '1.0';
        if ($message->hasAttachment()) {
            $boundaryMixed = md5(md5(time() . 'Mailer') . uniqid());
            $headers['Content-Type'] = "multipart/mixed; \r\n\tboundary=\"" . $boundaryMixed . "\"";
            $message->setBoundaryMixed($boundaryMixed);
        }
        $message->setBoundaryAlternative(md5(md5(time() . 'Mailer') . uniqid()));
        return $headers;
    }

    public function getBody(Message $message) {
    	return $message->hasAttachment() ? $this->createBodyWithAttachment($message) : $this->createBody($message);
	}

    /**
     * Create body
     * @param Message $message
     * @return string
     */
    protected function createBody(Message $message) {
        $result = '';
        $result .= 'Content-Type: multipart/alternative; boundary="' . $message->getBoundaryAlternative(). '"' . self::CRLF . self::CRLF;
        $result .= '--' . $message->getBoundaryAlternative() . self::CRLF;
        $result .= 'Content-Type: text/plain; charset="' . self::CHARSET . '"' . self::CRLF;
        $result .= "Content-Transfer-Encoding: base64" . self::CRLF . self::CRLF;
        $result .= chunk_split(base64_encode($message->getBody())) . self::CRLF . self::CRLF;
		$result .= '--' . $message->getBoundaryAlternative() . self::CRLF;
        $result .= 'Content-Type: text/html; charset="' . self::CHARSET . '"' . self::CRLF;
        $result .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
        $result .= chunk_split(base64_encode($message->getBody())) . self::CRLF . self::CRLF;
		$result .= '--' . $message->getBoundaryAlternative() . '--' . self::CRLF;
        return $result;
    }

    /**
     * Create body with attachment
     * @param Message $message
     * @return string
     */
    protected function createBodyWithAttachment(Message $message) {
        $result = self::CRLF . self::CRLF;
        $result .= '--' . $message->getBoundaryAlternative() . self::CRLF;
        $result .= 'Content-Type: multipart/alternative; boundary="' . $message->getBoundaryAlternative() . '"' . self::CRLF . self::CRLF;
		$result .= '--' . $message->getBoundaryAlternative() . self::CRLF;
        $result .= 'Content-Type: text/plain; charset="' . self::CHARSET . '"' . self::CRLF;
        $result .= 'Content-Transfer-Encoding: base64' . self::CRLF . self::CRLF;
        $result .= chunk_split(base64_encode($message->getBody())) . self::CRLF . self::CRLF;
        $result .= "--" . $this->boundaryAlternative . self::CRLF;
        $result .= 'Content-Type: text/html; charset="' . self::CHARSET . '"' . self::CRLF . self::CRLF;
        $result .= chunk_split(base64_encode($message->getBody())) . self::CRLF . self::CRLF;
		$result .= '--' . $message->getBoundaryAlternative() . '--' . self::CRLF;
        foreach ($message->getAttachment() as $name => $path) {
            $result .= self::CRLF;
            $result .= '--' . $this->boundaryMixed . self::CRLF;
            $result .= 'Content-Type: application/octet-stream; name="' . $name . '"' . self::CRLF;
            $result .= 'Content-Transfer-Encoding: base64' . self::CRLF;
            $result .= 'Content-Disposition: attachment; filename="' . $name . '"' . self::CRLF . self::CRLF;
            $result .= chunk_split(base64_encode(file_get_contents($path))) . self::CRLF;
        }
        $result .= self::CRLF . self::CRLF;
        $result .= '--' . $message->getBoundaryMixed() . '--' . self::CRLF;
        return $result;
    }

    /**
     * @param string $email
     * @param string|null $name
     * @return string
     */
    public function formatEmail($email, $name) {
        return empty($name)
            ? (string)$email
            : sprintf('=?utf-8?B?%s?= <%s>', base64_encode($name), $email);
    }

    /**
     * @param string $string
     * @return string
     */
    public function encode($string) {
        return sprintf('=?utf-8?B?%s?= ', base64_encode($string));
    }
}
