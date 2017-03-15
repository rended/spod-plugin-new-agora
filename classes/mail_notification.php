<?php

class SPODAGORA_CLASS_MailNotification extends OW_Component
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function sendEmailNotificationProcess($room_id)
    {
        $userService = BOL_UserService::getInstance();

        //GET ALL SUBSCRIBED USERS
        $users = SPODAGORA_BOL_Service::getInstance()->getSubscribedNotificationUsersForRoom($room_id);

        $room = SPODAGORA_BOL_Service::getInstance()->getAgoraById($room_id);
        $template_html = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_html.html';
        $template_txt  = OW::getPluginManager()->getPlugin('spodagora')->getCmpViewDir() . 'email_notification_template_text.html';
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);

        foreach($users as $user_id)
        {
            $user = $userService->findUserById($user_id["userId"]);

            if (empty($user))
                return false;

            $email = $user->email;

            try
            {
                $mail = OW::getMailer()->createMail()
                    ->addRecipientEmail($email)
                    ->setTextContent($this->getEmailContentText($room_id, $room, $user->username, $template_txt, $time))
                    ->setHtmlContent($this->getEmailContentHtml($room_id, $user_id["userId"], $room, $user->username, $template_html, $time))
                    ->setSubject("Something interesting is happening on Agora");

                OW::getMailer()->send($mail);
            }
            catch ( Exception $e )
            {
                //Skip invalid notification
            }

        }
    }

    private function getEmailContentHtml($room_id, $user_id, $room, $user, $template, $time)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);

        //USER AVATAR FOR THE NEW MAIL
        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user_id))[$user_id];
        $this->assign('userName', $user);
        $this->assign('string', OW::getLanguage()->text('spodpublic', 'email_txt_comment') . " <b><a href=\"" .
            OW::getRouter()->urlForRoute('spodagora.main')  . "/#!/" . $room_id . "\">" .
            $room->subject . "</a></b>");
        $this->assign('avatar', $avatar);
        $this->assign('time', $time);

        return parent::render();

    }

    private function getEmailContentText($room_id, $room, $user, $template, $time)
    {
        //SET EMAIL TEMPLATE
        $this->setTemplate($template);

        $this->assign('userName', $user);
        $this->assign('time', $time);
        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');
        $this->assign('string', "There is a new comment in the room <b>" . $room->subject . "</b>");
        $this->assign('url',"<a href='" . OW::getRouter()->urlForRoute('spodagora.main')  . "/#!/" . $room_id. "'>".$room->body."</a>");

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }
}