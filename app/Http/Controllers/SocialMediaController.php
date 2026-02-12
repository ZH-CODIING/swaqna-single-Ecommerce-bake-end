<?php

namespace App\Http\Controllers;

use App\Models\admin_token;
use App\Services\FacebookService;
use App\Services\InstgramService;
use App\Services\YoutubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SocialMediaController extends Controller
{
    public function ReadFacebookPosts(Request $request)
    {
        $tokens = admin_token::first();
        return FacebookService::ReadPosts($tokens['facebook_page_id'], $tokens['facebook_token']);
    }
    public function PostTOfacebook(Request $request)
    {
        $request->validate(['content' => 'required']);

        $tokens = admin_token::first();
        return FacebookService::postToPage($request->content, $tokens['facebook_page_id'], $tokens['facebook_token']);
    }
    public function PostImageTOfacebook(Request $request)
    {

        $request->validate(['content' => 'required', 'image' => 'image|max:4096']);
        $path = $request->file('image')->store('images', 'public');

        $url = Storage::url($path);
        $tokens = admin_token::first();
        return FacebookService::postImageToPage($request->content, $url, $tokens['facebook_page_id'], $tokens['facebook_token']);
    }

    public function PostTOInstagram(Request $request)
    {
        $request->validate(['content' => 'required', 'image' => 'image|max:4096']);

        $path = $request->file('image')->store('images', 'public');
        $url = Storage::url($path);
        $tokens = admin_token::first();
        return InstgramService::MakePost($url, $request->content, $tokens['instagram_account_id'], $tokens['instagram_token']);
    }

    public function ReadInstagramPosts(Request $request)
    {
        $tokens = admin_token::first();
        return InstgramService::ReadPosts($tokens['instagram_page_id'], $tokens['instagram_token']);
    }

    public function GetYoutubeCurrentChannel(Request $request)
    {
        $tokens = admin_token::first();
        return YoutubeService::GetCurrentChannel($tokens['youtube_token']);
    }
    public function GetYoutubeCurrentChannelVideos(Request $request)
    {
        $tokens = admin_token::first();
        return YoutubeService::GetCurrentChannelVideos($tokens['youtube_token'], $request->channelId);
    }
    public function GetYoutubeVideoStats(Request $request)
    {
        $request->validate(['video_id' => 'required']);

        $tokens = admin_token::first();
        return YoutubeService::GetVideoStats($tokens['youtube_token'], $request->video_id);
    }

    public function PostTOYoutube(Request $request)
    {
        $request->validate(['title' => 'required|max:500', 'video' => 'required|file|mimetypes:video/mp4|max:512000']);
        $tokens = admin_token::first();
        return YoutubeService::PublishVideo( $request->file('video'), $request->title,  $request->description, $tokens['youtube_token']);
    }

    public function YoutubeChangeVideoStatus(Request $request)
    {
        $request->validate(['newStatus' => 'required|max:500', 'videoId' => 'required']);
        $tokens = admin_token::first();
        return YoutubeService::ChangeVideoStatus($request->newStatus,$request->videoId,   $tokens['youtube_token']);
    }






}
