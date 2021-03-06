<?php

class SPODAGORA_CTRL_AgoraMain extends OW_ActionController
{

    private $COLORS = array("#FFFFFF", "#FFF3E0", "#FFE0B2", "#FFCC80", "#FFB74D", "#FFA726", "#FF9800", "#FF9800", "#F57C00", "#EF6C00", "#E65100");

    public function index()
    {
        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/main.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_main.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_main_new.css');

        OW::getLanguage()->addKeyForJs('spodagora', 'c_just_now');

        $raw_agora = SPODAGORA_BOL_Service::getInstance()->getAgora();
        $this->assign('agoras', $this->process_agora($raw_agora));

        $this->assign('isAuth_creation', OW::getAuthorization()->isUserAuthorized(OW::getUser()->getId(), 'spodagora', 'create_room'));
        $this->assign('user_id', OW::getUser()->getId());

        $this->initializeJS($raw_agora[0]);
    }

    private function initializeJS($first_agora)
    {
        $js = UTIL_JsGenerator::composeJsString('
            AGORAMAIN.user_room_notification = {$user_room_notification}           
            AGORAMAIN.notification_endpoint  = {$notification_endpoint}           
         ', array(
            'user_room_notification' => SPODAGORA_BOL_Service::getInstance()->getAllUserNotification(OW::getUser()->getId()),
            'notification_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'handleUserNotification')
        ));

        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript('AGORAMAIN.init('.$first_agora->id.');');
    }

    private function process_agora($agoras)
    {
//        $maxStat = SPODAGORA_BOL_Service::getInstance()->getMaxAgoraStat();

        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        $views_array = array();
        $comments_array = array();
        $opendata_array = array();

        foreach ($agoras as &$agora)
        {
            array_push($views_array, $agora->views);
            array_push($comments_array, $agora->comments);
            array_push($opendata_array, $agora->opendata);
        }

        sort($views_array);
        sort($comments_array);
        sort($opendata_array);

        foreach ($agoras as &$agora)
        {
            $view_index = array_search($agora->views, $views_array);
            $view_index = round($view_index / (count($views_array) - 1), 1) * 10;

            $comments_index = array_search($agora->comments, $comments_array);
            $comments_index = round($comments_index / (count($comments_array) - 1), 1) * 10;

            $opendata_index = array_search($agora->opendata, $opendata_array);
            $opendata_index = round($opendata_index / (count($opendata_array) - 1), 1) * 10;

            $comments = SPODAGORA_BOL_Service::getInstance()->getAllLevesCommentsFromAgoraId($agora->id);
            $users_id = array_diff(array_unique(array_map(function($comments) { return $comments->ownerId; }, $comments)), [$agora->ownerId]);
            $avatars  = BOL_AvatarService::getInstance()->getDataForUserAvatars($users_id);

//            $views_prctg    = ($agora->views*100/$maxStat["maxView"]);
//            $comments_prctg = ($agora->comments*100/$maxStat["maxComments"]);
//            $opendata_prctg = ($agora->opendata*100/$maxStat["maxOpendata"]);

            $agora->stat = array("views" => $view_index * 10, "viewsColor" => $this->COLORS[$view_index],
                "comments" => $comments_index * 10, "commentsColor" => $this->COLORS[$comments_index],
                "opendata" => $opendata_index * 10, "opendataColor" => $this->COLORS[$opendata_index]);
            $agora->timestamp = $this->process_timestamp($agora->timestamp, $today, $yesterday);
            $agora->avatars = $avatars;
            $agora->owner_avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($agora->ownerId));
            $agora->suggestions = SPODAGORA_BOL_Service::getInstance()->getAgoraSuggestedDataset($agora->id);
            $agora->unread_messages = count(SPODAGORA_BOL_Service::getInstance()->getUnreadCommentNumber($agora->id, OW::getUser()->getId()));
        }

        return $agoras;
    }

    private function process_timestamp($timestamp, $today, $yesterday)
    {
        $date = date('Ymd', strtotime($timestamp));

        if($date == $today)
            return date('H:i', strtotime($timestamp));

        if($date == $yesterday)
            return OW::getLanguage()->text('spodagora', 'yesterday'). " " . date('H:i', strtotime($timestamp));

        return date('H:i m/d', strtotime($timestamp));
    }

}