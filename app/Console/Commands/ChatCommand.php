<?php

namespace App\Console\Commands;

use App\Chat;
use App\Conf;
use App\ChatTweet;
use App\Library\TwitterBot;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Twitter;

class   ChatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitterbot:chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check mentions for chat keywords and reply';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $settings = Setting::findOrNew(1);
        if (!$settings->bot_power || !$settings->chat_power || !$settings->consumer_key || !$settings->consumer_secret || !$settings->access_token || !$settings->access_secret) {
            return;
        }

        $twitter = new TwitterBot();
        $twitter_dg = new Twitter($settings->consumer_key, $settings->consumer_secret, $settings->access_token, $settings->access_secret);

        $conf = Conf::findOrNew(1);
        $chat = Chat::get();
        if (isset($conf)) {
            $requestMethod = 'GET';
            $url = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';
            if ($conf->since_id) {
                $getfield = '?since_id=' . $conf->since_id;
                $mentions = json_decode($twitter->setGetfield($getfield)
                    ->buildOauth($url, $requestMethod)
                    ->performRequest());
            } else{
                $mentions = json_decode($twitter->buildOauth($url, $requestMethod)->performRequest());
            }

            $collection = collect($mentions);

            if ($collection->count() > 0) {
                foreach ($collection as $mention) {
                    foreach ($chat as $row) {
                        if (Carbon::parse($mention->created_at) > Carbon::parse($row->created_at)) {
                            if (isset($mention->text) && !empty($mention->text) && !$row->disable) {
                                if (mb_strpos($mention->text, $row->keyword) != false || mb_strpos($mention->text,
                                        $row->keyword) > 0 || strpos($mention->text, $row->keyword) > 0) {
                                    try {
                                        $reply_text = '@' . $mention->user->screen_name . ' ' . $row->reply;

                                        $twitter_dg->send($reply_text, null, ['in_reply_to_status_id' => $mention->id]);

                                        if (isset($collection->first()->id) && $collection->first()->id > 0) {
                                            if ($conf->since_id != $collection->first()->id) {
                                                $conf->since_id = $collection->first()->id;
                                                $conf->save();
                                            }
                                        }

                                        $tweet = new ChatTweet();
                                        $tweet->keyword = $row->keyword;
                                        $tweet->tweet_id = $collection->first()->id_str;
                                        $tweet->user_id = $collection->first()->user->id;
                                        $tweet->user_screen_name = $collection->first()->user->screen_name;
                                        $tweet->user_name = $collection->first()->user->name;
                                        $tweet->tweet_text = $collection->first()->text;
                                        $tweet->reply = $reply_text;
                                        $tweet->save();

                                    } catch (\Exception $e) {
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}