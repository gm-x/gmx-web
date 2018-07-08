<?php
namespace GameX\Core\Mail;

class Formatter {
    const CHARSET = "UTF-8";

    const CRLF = "\r\n";

    /**
     * @var Message
     */
    protected $message;

    /**
     * header multipart boundaryMixed
     */
    protected $boundaryMixed;

    /**
     * header multipart alternative
     */
    protected $boundaryAlternative;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Create mail header
     * @return array
     */
    protected function getHeaders() {
        $headers = [];
        $headers['Date'] = date('r');

        $headers['Return-Path'] = $this->message->getFromEmail();
        $headers['From'] = $this->formatEmail($this->message->getFromEmail(), $this->message->getFromName());

        $tmp = [];
        foreach ($this->message->getTo() as $email => $name) {
            $tmp[] = $this->formatEmail($email, $name);
        }
        $headers['To'] = implode(', ', $tmp);

        $tmp = [];
        foreach ($this->message->getCc() as $email => $name) {
            $tmp[] = $this->formatEmail($email, $name);
        }
        $headers['Cc'] = implode(', ', $tmp);

        $tmp = [];
        foreach ($this->message->getBcc() as $email => $name) {
            $tmp[] = $this->formatEmail($email, $name);
        }
        $headers['Bcc'] = implode(', ', $tmp);

        $headers['Reply-To'] = $this->formatEmail($this->message->getReplyEmail(), $this->message->getReplyName());

        $subject = $this->message->getSubject();
        $headers['Subject'] = !empty($subject) ? $this->encode($subject) : '';

        $headers['Message-ID'] = '<' . md5(uniqid()) . '@' . $this->message->getFromEmail() . '>';
        $headers['X-Priority'] = '3';
        $headers['MIME-Version'] = '1.0';
        if ($this->message->hasAttachment()) {
            $this->boundaryMixed = md5(md5(time() . 'Mailer') . uniqid());
            $headers['Content-Type'] = "multipart/mixed; \r\n\tboundary=\"" . $this->boundaryMixed . "\"";
        }
        $this->boundaryAlternative = md5(md5(time() . 'Mailer') . uniqid());
        return $headers;
    }

    /**
     * @brief createBody create body
     *
     * @return string
     */
    protected function createBody()
    {
        $result = "";
        $result .= "Content-Type: multipart/alternative; boundary=\"$this->boundaryAlternative\"" . self::CRLF;
        $result .= self::CRLF;
        $result .= "--" . $this->boundaryAlternative . self::CRLF;
        $result .= "Content-Type: text/plain; charset=\"" . self::CHARSET . "\"" . self::CRLF;
        $result .= "Content-Transfer-Encoding: base64" . self::CRLF;
        $result .= self::CRLF;
        $result .= chunk_split(base64_encode($this->message->getBody())) . self::CRLF;
        $result .= self::CRLF;
        $result .= "--" . $this->boundaryAlternative . self::CRLF;
        $result .= "Content-Type: text/html; charset=\"" . self::CHARSET . "\"" . self::CRLF;
        $result .= "Content-Transfer-Encoding: base64" . self::CRLF;
        $result .= self::CRLF;
        $result .= chunk_split(base64_encode($this->message->getBody())) . self::CRLF;
        $result .= self::CRLF;
        $result .= "--" . $this->boundaryAlternative . "--" . self::CRLF;
        return $result;
    }

    /**
     * @brief createBodyWithAttachment create body with attachment
     *
     * @return string
     */
    protected function createBodyWithAttachment()
    {
        $result = "";
        $result .= self::CRLF;
        $result .= self::CRLF;
        $result .= '--' . $this->boundaryMixed . self::CRLF;
        $result .= "Content-Type: multipart/alternative; boundary=\"$this->boundaryAlternative\"" . self::CRLF;
        $result .= self::CRLF;
        $result .= "--" . $this->boundaryAlternative . self::CRLF;
        $result .= 'Content-Type: text/plain; charset="' . self::CHARSET . '"' . self::CRLF;
        $result .= "Content-Transfer-Encoding: base64" . self::CRLF;
        $result .= self::CRLF;
        $result .= chunk_split(base64_encode($this->message->getBody())) . self::CRLF;
        $result .= self::CRLF;
        $result .= "--" . $this->boundaryAlternative . self::CRLF;
        $result .= "Content-Type: text/html; charset=\"" . self::CHARSET . "\"" . self::CRLF;
        $result .= "Content-Transfer-Encoding: base64" . self::CRLF;
        $result .= self::CRLF;
        $result .= chunk_split(base64_encode($this->message->getBody())) . self::CRLF;
        $result .= self::CRLF;
        $result .= "--" . $this->boundaryAlternative . "--" . self::CRLF;
        foreach ($this->message->getAttachment() as $name => $path) {
            $result .= self::CRLF;
            $result .= '--' . $this->boundaryMixed . self::CRLF;
            $result .= "Content-Type: application/octet-stream; name=\"" . $name . "\"" . self::CRLF;
            $result .= "Content-Transfer-Encoding: base64" . self::CRLF;
            $result .= "Content-Disposition: attachment; filename=\"" . $name . "\"" . self::CRLF;
            $result .= self::CRLF;
            $result .= chunk_split(base64_encode(file_get_contents($path))) . self::CRLF;
        }
        $result .= self::CRLF;
        $result .= self::CRLF;
        $result .= '--' . $this->boundaryMixed . '--' . self::CRLF;
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
