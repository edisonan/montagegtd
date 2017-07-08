<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Setting;
use App\Repositories\SettingRepository;

use Develpr\Phindle\Phindle;
use Develpr\Phindle\Content;
use Develpr\Phindle\OpfRenderer;

class KindleController extends Controller
{
    /**
     * The note repository instance.
     *
     * @var NoteRepository
     */
    protected $settings;
    

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct( SettingRepository $settings)
    {
        $this->middleware('auth', ['except' => ['welcome']]);

        $this->settings = $settings;
    }
    /**
     * Display a list of all of the user's task.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
    	$page_params = array();
    	
    	$setting = $this->settings->forUser($request->user());
    	
    	if(empty($setting)){
    		$setting = new Setting();
    	}
    	
        return view('kindles.index', [
            'setting' => $setting,
        ]);
    }
    
    
    /**
     * Destroy the given task.
     *
     * @param  Request  $request
     * @param  Task  $task
     * @return Response
     */
    public function test(Request $request)
    {
    	$user = $request->user();
    	$setting = $user->setting;
    	
    	if(!isset($setting->kindle_email) || empty($setting->kindle_email)){
    		echo 'empty kindle_email';exit;
    	}
    	
    	$phindle = new Phindle(array(
    			'title' => "Chaos Theory: Randomness is Beautiful",
    			'publisher' => "Develpr",
    			'creator' => 'Kevin Mitchell',
    			'language' => OpfRenderer::LANGUAGE_ENGLISH_US,
    			'subject' => 'Computers', //@see https://www.bisg.org/complete-bisac-subject-headings-2013-edition
    			'description' => 'A wonderfully random ebook',
    			'path'	=> config("app.storage_path") . '/ebooks', //The path that temp files will be stored, as well as the location of the final ebook mobi file
    			'isbn'  => '4242424242424242',
    			'staticResourcePath' => config("app.storage_path").'static/', //The absolute path to your static resources referenced in html (images, css, etc)
    			'cover'	=> '/images/14086750705_419447b9e1_b.jpg' , //The relative path of your cover image
    			'kindlegenPath' => '/usr/local/bin/kindlegen', //The path to the kindlegen utility
    			'downloadImages' => true, //Should images be downloaded from the web if found in your html?
    	));
    	$phindle->setAttribute('isbn', '4222222222222222');
    	for($i = 0; $i < 3; $i++)
    	{
    		/** @var Illuminate\View\View $html */
    		$html = 'okok'.$i;
    		$title = 'titletile'.$i;
    		$content = new Content();
    		$content->setHtml($html);
    		$content->setTitle($title);
    		$phindle->addContent($content);
    	}
    	//This is where all of the magic happens and the mobi file is actually generated
    	$phindle->process();
    	$path = config("app.storage_path") . '/ebooks/' . $phindle->getAttribute('uniqueId') . '.mobi';
    	
//     	header('Content-Type: application/octet-stream');
//     	header("Content-Transfer-Encoding: Binary");
//     	header("Content-disposition: attachment; filename=\"Chaos_Theory_Randomness_is_Beautiful.mobi\"");
//     	readfile($path);
    	
    	\Mail::send('emails.reminder', ['user'=>$user,'setting'=>$setting], function ($m) use ($user,$setting) {
    		$m->from('kindle@congcong.us', 'task.congcong.us');
    		$m->to($setting->kindle_email, $user->name)->subject('Send To Kindle');
    		$m->attach($path);
    	});
    	
    	echo 'success!';exit;
    }
}
