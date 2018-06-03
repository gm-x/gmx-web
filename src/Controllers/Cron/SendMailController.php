<?php
namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Models\Task;

class SendMailController extends BaseCronController {
    public function run(Task $task) {
        /** @var \GameX\Core\Mail\MailHelper $mail */
        $mail = self::getContainer('mail');
        $mailBody = $mail->render($task->data['type'], $task->data['params']);
        $mail->send([
            'name' => $task->data['email'],
            'email' => $task->data['email']
        ], $task->data['title'], $mailBody);
        return true;
    }
}
