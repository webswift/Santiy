@foreach($topicArticles as $topicArticle)
<tr>

    <td>
        <div class="media">
            <div class="media-body">
                <h4 class="text-primary"><a href="{{ URL::route('user.helptopics.detail', array($topicArticle->id)) }}">{{ $topicArticle->articleName }}</a></h4>
<!--                <p>{!! substr($topicArticle->text, 0, 50) !!}</p> -->
            </div>
        </div>
    </td>
</tr>
@endforeach