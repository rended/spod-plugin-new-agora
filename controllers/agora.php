<?php

class SPODAGORA_CTRL_Agora extends OW_ActionController
{
    private $agora;
    private $userId;
    private $avatars;
    private $agoraId;
    private $satisfied = 0;
    private $unsatisfied = 0;
    private $tot_comments = 0;
    private $users_id;

    public function index(array $params)
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $this->agoraId = $params['agora_id'];
        $this->agora = SPODAGORA_BOL_Service::getInstance()->getAgoraById($this->agoraId);
        $this->userId = OW::getUser()->getId();

        // AVATARS
        $all_level_comments = SPODAGORA_BOL_Service::getInstance()->getAllLevesCommentsFromAgoraId($this->agoraId);
        $this->users_id = array_unique($this->array_push_return(array_map(function($comments) { return $comments->ownerId; }, $all_level_comments), $this->userId) );

        $this->avatars  = BOL_AvatarService::getInstance()->getDataForUserAvatars($this->users_id);
        $this->assign('avatars', $this->avatars);

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodagora')->getRootDir() . 'master_pages/main.html');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agora_room.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'agoraJs.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'autogrow.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'socket_1_7_3.io.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'jquery.cssemoticons.min.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'jquery.cssemoticons.css');

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodagora')->getStaticCssUrl() . 'agora_room.css');

        OW::getDocument()->addScript('https://d3js.org/d3.v4.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'd3-tip.js');

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodagora')->getStaticJsUrl() . 'liquidFillGauge.js');

        OW::getLanguage()->addKeyForJs('spodagora', 'c_just_now');
        OW::getLanguage()->addKeyForJs('spodagora', 'c_reply');
        OW::getLanguage()->addKeyForJs('spodagora', 'g_datalets');
        OW::getLanguage()->addKeyForJs('spodagora', 'g_datasets');
        OW::getLanguage()->addKeyForJs('spodagora', 'g_time');

        SPODAGORA_BOL_Service::getInstance()->addAgoraRoomStat($this->agoraId, 'views');
        $raw_comments = SPODAGORA_BOL_Service::getInstance()->getCommentList($this->agoraId);
        $this->tot_comments = count($raw_comments);
        $this->assign('comments', $this->process_comment($raw_comments));

        $raw_unread_comments = SPODAGORA_BOL_Service::getInstance()->getUnreadComment($this->agoraId, $this->userId);
        $this->assign('unread_comments_count', count($raw_unread_comments));
        $this->assign('unread_comments', $this->process_unread_comment($raw_unread_comments));

        $notification = SPODAGORA_BOL_Service::getInstance()->getUserNotification($this->agoraId, OW::getUser()->getId());
        $this->assign('user_notification', empty($notification) ? '' : 'checked');

        // AGORA
        $this->assign('agora', $this->agora);

        $this->initializeJS();
    }

    private function process_unread_comment($unread_commnets)
    {
        $max_day        = 7;
        $today          = date_create('today');
        $unread_section = array();

        for($i = 0; $i < $max_day; $i++)
            $unread_section[OW::getLanguage()->text('spodagora', date_create('today - '.$i.' day')->format('l'))] = array();

        foreach ($unread_commnets as &$comment)
        {
            if(date_diff($today, date_create($comment->timestamp))->d > 7)
                break;

            $comment->comment         = strip_tags($comment->comment);
            $comment->username        = $this->avatars[$comment->ownerId]["title"];
            $comment->owner_url       = $this->avatars[$comment->ownerId]["url"];
            $comment->avatar_url      = $this->avatars[$comment->ownerId]["src"];
            $section                  = OW::getLanguage()->text('spodagora', date('l', strtotime($comment->timestamp)));
            $comment->timestamp       = date('H:i', strtotime($comment->timestamp));
            $comment->sentiment_class = $comment->sentiment == 0 ? 'neutral' : ($comment->sentiment == 1 ? 'satisfied' : 'dissatisfied');

            array_push($unread_section[$section], $comment);
        }

        return $unread_section;
    }

    private function initializeJS()
    {
        $avatars = $this->avatars;

        if(empty($avatars[$this->userId]))
        {
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($this->userId));
        }

        $js = UTIL_JsGenerator::composeJsString('
            AGORA.roomId = {$roomId}
            AGORA.agora_comment_endpoint = {$agora_comment_endpoint}
            AGORA.agora_static_resource_url = {$agora_static_resource_url}
            AGORA.username = {$username}
            AGORA.user_url = {$user_url}
            AGORA.user_avatar_src = {$user_avatar_src}
            AGORA.user_id = {$user_id}
            AGORA.agora_nested_comment_endpoint = {$agora_nested_comment_endpoint}
            AGORA.user_notification_url = {$user_notification_url} 
            AGORA.datalet_graph = {$datalet_graph}
            AGORA.sat_prctg = {$sat_prctg}
            AGORA.unsat_prctg = {$unsat_prctg}
            AGORA.search_url = {$search_url}
            AGORA.user_friendship = {$user_friendship}
            AGORA.users_avatar = {$users_avatar}
            AGORA.get_site_tag_endpoint = {$get_site_tag_endpoint}
         ', array(
            'roomId' => $this->agoraId,
            'agora_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'addComment'),
            'agora_static_resource_url' =>  OW::getPluginManager()->getPlugin('spodagora')->getStaticUrl(),
            'username' => $avatars[$this->userId]["title"],
            'user_url' => $avatars[$this->userId]["url"],
            'user_avatar_src' => $avatars[$this->userId]["src"],
            'user_id' => $this->userId,
            'agora_nested_comment_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'getNestedComment'),
            'user_notification_url' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'handleUserNotification'),
            'datalet_graph' => $this->agora->datalet_graph,
            'sat_prctg' => ($this->satisfied*100)/($this->tot_comments == 0 ? 1 : $this->tot_comments ),
            'unsat_prctg' => ($this->unsatisfied*100)/($this->tot_comments == 0 ? 1 : $this->tot_comments ),
            'search_url' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'getSearchResult'),
            'user_friendship' => SPODAGORA_BOL_Service::getInstance()->getAgoraFriendship($this->users_id),
            'users_avatar' => $this->avatars,
            'get_site_tag_endpoint' => OW::getRouter()->urlFor('SPODAGORA_CTRL_Ajax', 'getSiteMetaTags')
        ));

        OW::getDocument()->addOnloadScript($js);
        OW::getDocument()->addOnloadScript('AGORA.init();');
    }

    private function process_comment(&$comments)
    {
        $today = date('Ymd');
        $yesterday = date('Ymd', strtotime('yesterday'));

        foreach ($comments as &$comment)
        {
            $comment->username      = $this->avatars[$comment->ownerId]["title"];
            $comment->owner_url     = $this->avatars[$comment->ownerId]["url"];
            $comment->avatar_url    = $this->avatars[$comment->ownerId]["src"];
            $comment->total_comment = isset($comment->total_comment) ? $comment->total_comment : 0;
            $comment->timestamp     = $this->process_timestamp($comment->timestamp, $today, $yesterday);

            $comment->css_class       = $this->userId == $comment->ownerId ? 'agora_right_comment' : 'agora_left_comment';

            switch ($comment->sentiment)
            {
                case 0 : $comment->sentiment_class = 'neutral'; break;
                case 1 : $comment->sentiment_class = 'satisfied'; $this->satisfied+=1; break;
                case 2 : $comment->sentiment_class = 'dissatisfied'; $this->unsatisfied+=1; break;
            }

            if (!empty($comment->component)) {
                $comment->datalet_class  = 'agora_fullsize_datalet';
                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'. $comment->component . '",
                                                                    ' . $comment->params . ',
                                                                    ['. $comment->fields . '],
                                                                    undefined,
                                                                    "agora_datalet_placeholder_' . $comment->id . '");');
            }

        }

        return $comments;
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

    private function array_push_return($array, $val)
    {
        array_push($array, $val);
        return $array;
    }

}