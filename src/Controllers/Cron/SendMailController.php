<?php
namespace GameX\Controllers\Cron;

use \GameX\Core\BaseCronController;
use \GameX\Models\Task;
use \GameX\Core\Jobs\JobResult;
use \GameX\Core\Mail\Email;

class SendMailController extends BaseCronController {
    public function run(Task $task) {
        /** @var \GameX\Core\Mail\Helper $mail */
        $mail = self::getContainer('mail');
        $mailBody = $mail->render($task->data['type'], $task->data['params']);
        $mail->send(new Email($task->data['email'], $task->data['user']), $task->data['title'], $mailBody);
        return new JobResult(true);
    }
}
