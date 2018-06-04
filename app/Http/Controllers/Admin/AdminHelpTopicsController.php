<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpArticle;
use App\Models\HelpTopic;
use DateTime;
use Input;
use Redirect;
use Request;
use Session;
use View;

class AdminHelpTopicsController extends Controller {

    public function helpTopics($postedTopicID = null) {

        $data['postedTopicID'] = $postedTopicID;

        $data['helpTopicMenuActive'] = "active";
        $data['topicLists'] = HelpTopic::all();
        $data['successMessage'] 	= Session::get('successMessage');
        $data['successMessageClass'] 	= Session::get('successMessageClass');

        $topicArticles = HelpArticle::all();
        $data['topicArticles'] = $topicArticles;
        $data['totalResultCount'] = $topicArticles->count();

        return View::make('admin/helptopic/helptopics', $data);
    }

    public function addNewTopic()
    {
        $data = [];
        if(Request::ajax())
        {
            $topicName = Input::get('topicName');

            $newTopicName = new HelpTopic;
            $newTopicName->topic = $topicName;
            $newTopicName->timeCreated = new DateTime;
            $newTopicName->save();

            Session::flash('successMessage', 'Article Topic Successfully Created.');
            Session::flash('successMessageClass', 'success');
            $data['success'] = 'success';
        }

        return json_encode($data);
    }

    public function ajaxTopicArticle()
    {
        if(Request::ajax())
        {
            $topicID = Input::get('topicID');


            $topicArticles = HelpArticle::where('topicID', $topicID)->get();


            $data['topicArticles'] = $topicArticles;
            $output['totalResultCount'] = $topicArticles->count();
            $output['success'] = "success";
        }

        $output['results'] = View::make('admin/helptopic/ajaxtopicarticle', $data)->render();

        return json_encode($output);
    }

    public function addOrEditArticle($action , $id = null)
    {
        if($action === 'add')  {
            $data['helpTopicMenuActive'] = "active";
            $data['topicLists'] = HelpTopic::all();
            $data['alreadySelectedValue'] = $id;

            return View::make('admin/helptopic/addarticle', $data);
        }
        else if($action === 'edit') {
            $data['helpTopicMenuActive'] = "active";
            $data['topicLists'] = HelpTopic::all();
            $data['articleDetails'] = HelpArticle::find($id);

            return View::make('admin/helptopic/editarticle', $data);
        }
        else{
            Redirect::to('/helptopics');
        }
    }

    public function addNewArticle() {
        if(Request::ajax())
        {
            $articleName = Input::get('articleName');
            $topicID = Input::get('topicID');
            $keywords = Input::get('keywords');
            $articleText = Input::get('articleText');

            $newArticle = new HelpArticle;
            $newArticle->topicID = $topicID;
            $newArticle->articleName = $articleName;
            $newArticle->text = $articleText;
            $newArticle->keywords = $keywords;
            $newArticle->timeCreated = new DateTime;
            $newArticle->save();

            Session::flash('successMessage', 'Article Successfully Created.');
            Session::flash('successMessageClass', 'success');
            $data['success'] = 'success';
        }

        return json_encode($data);
    }

    public function editArticle()
    {
        if(Request::ajax())
        {
            $articleID = Input::get('articleID');
            $articleName = Input::get('articleName');
            $topicID = Input::get('topicID');
            $keywords = Input::get('keywords');
            $articleText = Input::get('articleText');

            $editArticle = HelpArticle::find($articleID);
            $editArticle->topicID = $topicID;
            $editArticle->articleName = $articleName;
            $editArticle->text = $articleText;
            $editArticle->keywords = $keywords;
            $editArticle->timeCreated = new DateTime;
            $editArticle->save();

            Session::flash('successMessage', 'Article Successfully Updated.');
            Session::flash('successMessageClass', 'success');
            $data['success'] = 'success';
        }

        return json_encode($data);
    }

    public function deleteArticle()
    {
        if(Request::ajax())
        {
            $articleID = Input::get('articleID');

            HelpArticle::find($articleID)->delete();

            Session::flash('successMessage', 'Article Successfully Deleted.');
            Session::flash('successMessageClass', 'danger');
            $data['success'] = "success";
        }

        return json_encode($data);
    }

    public function deleteTopic()
    {
        if(Request::ajax())
        {
            $topicID = Input::get('topicID');

            HelpTopic::find($topicID)->delete();

            Session::flash('successMessage', 'Topic Successfully Deleted.');
            Session::flash('successMessageClass', 'danger');
            $data['success'] = "success";
        }

        return json_encode($data);
    }

}