<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PushMessage;
use App\Models\User;
use Auth;
use DateTime;
use Input;
use Redirect;
use Request;
use URL;
use View;


class PushMessageUserController extends Controller {

    public function index($pageNumber = 1) {
        $userID = $this->user->id;
        $userType = $this->user->userType;

        if($pageNumber < 1) {
            $pageNumber = 1;
        }

        $take = 10;
        $skip = ($pageNumber-1) * $take ;

        $data['totalMessages'] = PushMessage::where('receiver', $userID)->count();

        if($userType === 'Team') {
            $userManger  = $this->user->manager;

            $data['teamMembers'] =  User::select(['users.firstName', 'users.id'])
	            ->Where('id', '!=', $userID)
                ->where('manager', $userManger)
                ->orWhere('id', '=',$userManger)
                ->get();
        }
        else if($userType === 'Multi') {
            $userManger = $userID;

            $data['teamMembers'] =  User::select(['users.firstName', 'users.id'])
                ->where('manager', $userManger)
                ->get();
        }

        $data['allMessages'] = PushMessage::select('pushmessages.id AS messageID', 'pushmessages.message', 'users.firstName', 'users.lastName', 'users.id as userID', 'pushmessages.time')
                                         ->join('users', 'users.id', '=', 'pushmessages.sender')
                                         ->where('receiver', $userID)
                                         ->orderBy('pushmessages.time', 'DESC')
                                         ->take($take)
                                         ->skip($skip)
                                         ->get();

        $data['pushMessageMenuActive']  = 'nav-active active';
        $data['pushMessageStyleActive'] =  'display: block';
        $data['pushMessageMenuStyleActive'] =  'active';

        $data['pageNumber'] = $pageNumber;
        return View::make('site/pushmessage/pushmessages', $data);
    }

    public function messageDetail($messageID)
    {
        $userID = Auth::user()->get()->id;

        $messageDetails = PushMessage::select('pushmessages.id AS messageID', 'pushmessages.message', 'users.firstName', 'users.lastName', 'users.id as userID', 'pushmessages.time', 'pushmessages.receiver')
                                    ->join('users', 'users.id', '=', 'pushmessages.sender')
                                    ->where('pushmessages.id', $messageID)
                                    ->first();

        if($messageDetails->receiver == $userID)
        {
            $userType = Auth::user()->get()->userType;

            if($userType === 'Team' || $userType === 'Multi')
            {

                if($userType === 'Team')
                {
                    $userManger  = Auth::user()->get()->manager;

                    $data['teamMembers'] =  User::select(['users.firstName', 'users.id'])
                        ->Where('id', '!=', $userID)
                        ->where('manager', $userManger)
                        ->orWhere('id', '=',$userManger)
                        ->get();
                }else if($userType === 'Multi')
                {
                    $userManger = $userID;

                    $data['teamMembers'] =  User::select(['users.firstName', 'users.id'])
                        ->where('manager', $userManger)
                        ->get();
                }



            }

            $data['messageDetails'] = $messageDetails;

            $data['pushMessageMenuActive']  = 'nav-active active';
            $data['pushMessageStyleActive'] =  'display: block';
            return View::make('site/pushmessage/messagedetail', $data);
        }else{
            Redirect::to('user.pushmessage');
        }


    }

    public function generateMessage()
    {
        if(Request::ajax())
        {
            $userIDs = Input::get('usersIDs');
            $message = Input::get('message');
            $userID = Auth::user()->get()->id;

            foreach($userIDs as $receiver)
            {
                $newMessage = new PushMessage;
                $newMessage->message = $message;
                $newMessage->sender = $userID;
                $newMessage->receiver = $receiver;
                $newMessage->time = new DateTime;
                $newMessage->status = "Sent";
                $newMessage->save();
            }

            $data['success'] = 'success';
        }

        return json_encode($data);
    }

    public function pushMessageNotification()
    {
        if(Request::ajax())
        {
			if(env('APP_DEBUG', false) == true) {
				\Debugbar::disable();
			}

            $userID = Auth::user()->get()->id;

            $todayTime = new DateTime;

            $messages = PushMessage::select('pushmessages.id AS messageID', 'pushmessages.message', 'users.firstName', 'users.lastName')
                                   ->join('users', 'users.id', '=', 'pushmessages.sender')
                                   ->where('receiver', $userID)
                                   ->where('time','<=', $todayTime)
                                   ->where('status', '=', 'Sent')
                                   ->get();

            $notificationArray = [];

            foreach($messages as $message)
            {

                $notificationArray[] = [
                    'title' => '<a href="'.URL::route('user.pushmessage.messagedetail', [$message->messageID]).'">'.$message->firstName . ' ' . $message->lastName . ' Says:</a>',
                    'text'  => substr($message->message, 0, 80),
                    'sticky'=> true
                ];

                PushMessage::where('id', $message->messageID)
                           ->update(['status' => 'Shown']);
            }

            $data['success'] = 'success';
            $data['notifications'] = $notificationArray;
        }

        return json_encode($data);
    }

    public function deletePushMessage()
    {
        if(Request::ajax())
        {
            $userID = Auth::user()->get()->id;

            $messageID = Input::get('messageID');

            $isActionValid = PushMessage::where('receiver', '=', $userID)
                                          ->where('id', '=', $messageID)
                                          ->count();

            if($isActionValid > 0)
            {
                PushMessage::find($messageID)->delete();
                $data['success'] = "success";
            }else{
                $data["success"] = "fail";
            }
        }

        return json_encode($data);
    }
}
