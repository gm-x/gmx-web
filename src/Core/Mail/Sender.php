<?php
namespace GameX\Core\Mail;

class Sender {
    protected $host;
    protected $port;
    protected $secure;

    public function __construct($host, $port, $secure) {
    }

    public function send(Formatter $formatter) {

    }

    protected function connect() {
        $host = ($this->secure == 'ssl' ? 'ssl://' : '') . $this->host;
        $socket = @fsockopen($host, $this->port);
        //set block mode
        //    stream_set_blocking($this->smtp, 1);
        if (!$host){
            throw new SMTPException("Could not open SMTP Port.");
        }
        $code = $this->getCode();
        if ($code !== '220'){
            throw new CodeException('220', $code, array_pop($this->resultStack));
        }
        return $this;
    }
}
