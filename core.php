<?php
	set_time_limit(0);
	ini_set('display_errors',true);//Just in case we get some errors, let us know....

    new WallPapper();


class WallPapper{
	private $rssFeedUrl = "http://feeds.feedburner.com/wlppr";

	private $width;
	private $height;
	private $fileName = "";
    private $fileDir = "/tmp/";
    private $fileUrl;

	function __construct(){
		$this->getScreenResolution();
	    $feed = $this->getRSSFeed();
        $this->extractImageUrl( $feed );
        $this->downloadWallpaper();
        $this->setWallpaper();
	}

    private function getScreenResolution(){
		$res = shell_exec("system_profiler | grep Resolution");
		preg_match_all("/Resolution: ([0-9]*) x ([0-9]*)/", $res, $arr, PREG_PATTERN_ORDER);
		$this->width  = $arr[1][0];
		$this->height = $arr[2][0];
	}

	private function getRSSFeed(){
		$ch = 	curl_init( $this->rssFeedUrl );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
		$data = curl_exec($ch);
				curl_close($ch);
		return $data;
	}

	private function extractImageUrl( $data ){
		$doc = new SimpleXmlElement( $data, LIBXML_NOCDATA);

		$html = substr($doc->entry->content, 0, strpos($doc->entry->content,">".$this->width."x".$this->height."<")-1 );
		$this->fileUrl = substr($html, strrpos($html, "href")+6 );

	    $this->fileName = substr($this->fileUrl, strrpos($this->fileUrl, '/')+1 );

        if(! strlen($this->fileName) ){
            exit("Can not find the url of the file to download. Quit");
        }
    }

	private function downloadWallpaper(){
		$fp = fopen ( $this->fileDir . $this->fileName, 'w+');
		$ch = curl_init( $this->fileUrl );
		curl_setopt($ch, CURLOPT_TIMEOUT, 50);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}

	private function setWallpaper(){

//		$d = '\'{default = {ChangePath = "/Users/krichard/Pictures/Wallpapers";ChooseFolderPath = "/Users/krichard/Pictures/Wallpapers";CollectionString = Wallpapers;ImageFileAlias = <00000000 00e00003 00000000 c2cc314a 0000482b 00000000 00089e0c 001be568 0000c2fe 8ab30000 00000920 fffe0000 00000000 0000ffff ffff0001 00100008 9e0c0007 4cea0007 4cb40013 52b2000e 00260012 00740068 00650065 006d0070 00690072 0065005f 00310036 00380030 002e006a 00700067 000f001a 000c004d 00610063 0069006e 0074006f 00730068 00200048 00440012 00355573 6572732f 6b726963 68617264 2f506963 74757265 732f5761 6c6c7061 70657273 2f746865 656d7069 72655f31 3638302e 6a706700 00130001 2f000015 0002000f ffff0000 >;ImageFilePath = "@@@@@";Placement = Crop;TimerPopUpTag = 6;};}\'';
		$d = '\'{default = {ImageFilePath = "@@@@@";Placement = Center;};}\'';
		$cmd = "defaults write com.apple.Desktop Background ";
		shell_exec( $cmd . str_replace('@@@@@', $this->fileDir . $this->fileName, $d) );
		shell_exec( "killall Dock");
	}
};






