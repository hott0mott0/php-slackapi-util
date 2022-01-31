<?php 
namespace SlackAPIUtil;

/**
 * @author Tomohito Hotta
 * 
 * ※Event Subscriptionのエンドポイント認証について
 * 「 <?php echo file_get_contents('php://input'); 」
 * とだけ書いたPHPスクリプトファイルをエンドポイントにして認証すれば通ります。
 * 
 */

class Utility
{

    /**
     * 所属するワークスペース内に存在するpublicチャンネルの一覧を取得します。
     * 
     * $token (String)
     * 
     * @return
     */
    public function getChannelList($token) 
    {
        //SlackのWPワークスペース内にあるチャンネルの情報を取得

       $ch = curl_init();
       curl_setopt_array($ch, [
           CURLOPT_RETURNTRANSFER => 1,
           CURLOPT_URL => "https://slack.com/api/conversations.list?token=$token&exclude_archived=true&limit=500&types=public_channel"
       ]);
       $resp = curl_exec($ch);
       curl_close($ch);
    
       $decodedData = json_decode($resp, true);
       $channel_list = $decodedData['channels'];
    
    
       // 名前に特定の文字列が含まれるチャンネルを除外する場合の処理
       //
       // $i = 0;
       // foreach($channel_list as $key){
       //     if(strpos($key['name'], 'times') !== false){
       //         unset($channel_list[$i]);
       //     }
       //     $i++;
       // }
    
       $channel_list = array_values($channel_list);
    
       //error_log(print_r($channel_list, true));
       return $channel_list;
    }


    /**
     * 特定のチャンネルからメッセージを取得します。
     * 
     * $token, $channelId (String)
     * 
     * @return
     */
    public function getMessage($token, $channelId) 
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => "https://slack.com/api/conversations.history?token=$token&channel=$channelId&inclusive=true"
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $decodedData = json_decode($resp, true);
        return $decodedData;
    }


    /**
     * 特定の期間に特定のチャンネルに投稿されたメッセージを取得します。
     * 
     * $token, $channelId (String)
     * $before_time, $near_time (DateTime, format to UnixTime)
     * 
     * @return
     */
    public function getMessageInPeriod($token, $channelId, $before_time, $near_time) 
    {
         //Slackの特定のチャンネルから、今日から〇日前までのメッセージを取得

         $ch = curl_init();
         curl_setopt_array($ch, [
             CURLOPT_RETURNTRANSFER => 1,
             CURLOPT_URL => "https://slack.com/api/conversations.history?token=$token&channel=$channelId&inclusive=true&oldest=$before_time&latest=$near_time"
         ]);
         $resp = curl_exec($ch);
         curl_close($ch);
 
         $decodedData = json_decode($resp, true);
         return $decodedData;
    }


    /**
     * SlackAPIの設定画面で取得したWebHookURLを用いて、メッセージを特定のチャンネルに送信します。
     * 
     * $hookURL, $rawJson (String)
     * 
     * @return
     */
    public function sendMessage($hookURL, $rawJson) 
    {
        //CURLでSlackに送信

        $ch = curl_init($hookURL);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $rawJson );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}