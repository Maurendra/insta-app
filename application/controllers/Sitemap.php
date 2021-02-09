<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sitemap extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		date_default_timezone_set('Asia/Jakarta');
		$this->_init();
	}

	private function _init()
	{
  }
  
  public function index(){
    redirect('sitemap/xml');
  }

	public function xml(){
    $this->output->set_content_type('text/xml');
    $xml = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
		$now =  date('Y-m-d H:i:s');

    // Web App
    $xml .= "<url>";
    $xml .= "<loc>".base_url()."</loc>";
    $xml .= "<priority>1.0</priority>";
    $xml .= "<lastmod>".$now."</lastmod>";
    $xml .= "</url>";

    // 
    $xml .= "<url>";
    $xml .= "<loc>".base_url('/web/download')."</loc>";
    $xml .= "<priority>0.7</priority>";
    $xml .= "<lastmod>".$now."</lastmod>";
    $xml .= "</url>";
    
    // 
    $xml .= "<url>";
    $xml .= "<loc>".base_url('/web/faq')."</loc>";
    $xml .= "<priority>0.7</priority>";
    $xml .= "<lastmod>".$now."</lastmod>";
    $xml .= "</url>";

    // 
    $xml .= "<url>";
    $xml .= "<loc>".base_url('/web/pricing')."</loc>";
    $xml .= "<priority>0.7</priority>";
    $xml .= "<lastmod>".$now."</lastmod>";
    $xml .= "</url>";

    // 
    $xml .= "<url>";
    $xml .= "<loc>".base_url('/web/pricing')."</loc>";
    $xml .= "<priority>0.7</priority>";
    $xml .= "<lastmod>".$now."</lastmod>";
    $xml .= "</url>";

    // 
    $xml .= "<url>";
    $xml .= "<loc>".base_url('/web/register')."</loc>";
    $xml .= "<priority>0.7</priority>";
    $xml .= "<lastmod>".$now."</lastmod>";
    $xml .= "</url>";
    
    // 
    $xml .= "<url>";
    $xml .= "<loc>".base_url('/web/login')."</loc>";
    $xml .= "<priority>0.7</priority>";
    $xml .= "<lastmod>".$now."</lastmod>";
    $xml .= "</url>";

    $xml .= '</urlset>';
    $this->output->set_output($xml);
  }
  
  private function generateSeoURL($string, $wordLimit = 0){
    $separator = '-';
    
    if($wordLimit != 0){
        $wordArr = explode(' ', $string);
        $string = implode(' ', array_slice($wordArr, 0, $wordLimit));
    }

    $quoteSeparator = preg_quote($separator, '#');

    $trans = array(
        '&.+?;'                    => '',
        '[^\w\d _-]'            => '',
        '\s+'                    => $separator,
        '('.$quoteSeparator.')+'=> $separator
    );

    $string = strip_tags($string);
    foreach ($trans as $key => $val){
        $string = preg_replace('#'.$key.'#i'.(UTF8_ENABLED ? 'u' : ''), $val, $string);
    }

    $string = strtolower($string);

    return trim(trim($string, $separator));
  }

}
