@foreach($topicArticles as $topicArticle)
<tr>
    <td>
        <div class="ckbox ckbox-success">
            <input type="checkbox" id="checkbox3">
            <label for="checkbox3"></label>
        </div>
    </td>
    <td>
        <a class="star"><i class="glyphicon glyphicon-star"></i></a>
    </td>
    <td>
        <div class="media">
            <a href="#" class="pull-left">
                <img alt="" src='{{ URL::asset("assets/images/photos/user1.png") }}' class="media-object">
            </a>
            <div class="media-body">
                <span class="media-meta pull-right" onclick="deleteThisArticle({{ $topicArticle->id }})">x</span>
                <h4 class="text-primary"><a href="{{ URL::route('admin.helptopics.addoreditarticle', array('edit', $topicArticle->id)) }}">{{ $topicArticle->articleName }}</a></h4>
            </div>
        </div>
    </td>
</tr>
@endforeach