<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Feed;
use App\Repositories\FeedRepository;
use App\Category;
use App\Repositories\CategoryRepository;
use App\Article;

use ArandiLopez\Feed\Factories\FeedFactory; //use SimplePie to parse RSS feeds, see: https://github.com/arandilopez/laravel-feed-parser


class FeedController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $feeds;
    protected $categorys;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(CategoryRepository $categorys, FeedRepository $feeds)
    {
        $this->middleware('auth');

        $this->feeds = $feeds;
        $this->categorys = $categorys;
    }

    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	$feeds = $this->feeds->forUser($request->user());
    	$categorys = $this->categorys->forUser($request->user());
    	
        return view('feeds.index', [
            'feeds' => $feeds,
        	'categorys' => $categorys,
        ]);
    }
    
    /**
     * Create a new note.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'feed_name' => 'required',
        	'url' => 'required',
        	'category_id' => 'required',
        ]);
        
        $category = $this->categorys->forCategoryId($request->user(),$request->category_id);
        if(empty($category)){
        	echo 'error:'.$request->category_id;exit;
        }

        $feed = $request->user()->feeds()->create([
            'feed_name' => $request->feed_name,
        	'url' => $request->url,
        	'category_id' => $request->category_id,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/feeds');
        }
        
    }

    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function destroy(Request $request, Feed $feed)
    {
        $this->authorize('destroy', $feed);

        $feed->delete();

        if ($request->ajax() || $request->wantsJson()) {
        	$resp = $this->responseJson(self::OK_CODE);
        	return response($resp);
        } else {
        	return redirect('/feeds');
        }
    }
    
    public function updateFeed(Request $request){
    	$feeds = Feed::orderBy('updated_at', 'asc')->take(15)->get();
    	
    	if (! empty($feeds)) {
    		foreach ($feeds as $feed) {
    			//update feed, see update function
    			$this->update($feed);
    		}
    	}
    	
    }
    
    public function update(Feed $Feed)
    {
    	//set previous week
    	$previousweek = date('Y-m-j H:i:s', strtotime('-7 days'));
    
    	echo $Feed->url.'<br>';
    	$feedFactory = new FeedFactory(['cache.enabled' => false]);
    	$feeder = $feedFactory->make($Feed->url);
    	$simplePieInstance = $feeder->getRawFeederObject();
    
    	//only add articles and update feed when results are found
    	if (!empty($simplePieInstance)) {
    
    		foreach ($simplePieInstance->get_items() as $item) {
    			//count the number of items that already exist in the database with the item url and feed_id
    			$results_url = Article::where(['user_id'=>$Feed->user_id, 'feed_id' => $Feed->id, 'url' => $item->get_permalink()])->count();
    			$results_title = Article::where(['user_id'=>$Feed->user_id, 'feed_id' => $Feed->id, 'subject' => $item->get_title()])->count();
    			$date = $item->get_date('Y-m-j H:i:s');
    
    			//add new article if no results are found and article date is no older than one week
    			if ($results_url == 0 && $results_title == 0 && ! (strtotime($date) < strtotime($previousweek))) {
    				$article = new Article;
    
    				//get article content
    				$article->feed_id = $Feed->id;
    				$article->status = 'unread';
    				$article->url = $item->get_permalink();
    				$article->subject = $item->get_title();
    				$article->content = $item->get_description();
    				$article->published = $item->get_date('Y-m-j H:i:s');
    				
    				$article->user_id = $Feed->user_id;
    
    				//get URL of first image
    				//TODO: replace with SimplePie str_get_html function, see: http://stackoverflow.com/questions/9865130/getting-image-url-from-rss-feed-using-simplepie
    				$description =  $item->get_description();
    				preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $description, $image);
    				if (array_key_exists('src', $image)) {
    					$article->image_url = $image['src'];
    				}
    
    				//save article content to database
    				$article->save();
    
    				echo '- '.$item->get_title().'<br>';
    			}
    		}
    
    		//update feed updated_at record
    		Feed::where('id', $Feed->id)->update(['updated_at' => date('Y-m-j H:i:s')]);
    		Feed::where('id', $Feed->id)->update(['feed_desc' => $simplePieInstance->get_description()]);
    		Feed::where('id', $Feed->id)->update(['favicon' => $simplePieInstance->get_image_url()]);
    	}
    }
}
