<?php
/************************************/
/* global functions                 */
/************************************/
function hex2dec($color = "#000000"){
	$tbl_color = array();
	$tbl_color['R']=hexdec(substr($color, 1, 2));
	$tbl_color['G']=hexdec(substr($color, 3, 2));
	$tbl_color['B']=hexdec(substr($color, 5, 2));
	return $tbl_color;
}

function px2mm($px){
	return $px*25.4/72;
}

function txtentities($html){
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	return strtr($html, $trans);
}


/************************************/
/* main class createPDF             */
/************************************/
class createPDF {

	function __construct($_html,$_title,$_articleurl,$_author,$_date) {
		// main vars
		$this->html=$_html;               // html text to convert to PDF
		$this->title=$_title;             // article title
		$this->articleurl=$_articleurl;   // article URL
		$this->author=$_author;           // article author
		$this->date=$_date;               // date being published
		// other options
		$this->from='iso-8859-2';         // input encoding
		$this->to='cp1250';               // output encoding
		$this->useiconv=false;            // use iconv
		$this->bi=true;                   // support bold and italic tags
	}

	function _convert($s) {
		if ($this->useiconv) 
			return iconv($this->from,$this->to,$s); 
		else 
			return $s;
	}

	function run($file) {
		// change some win codes, and xhtml into html
		$str=array(
		'<br />' => '<br>',
		'<hr />' => '<hr>',
		'[r]' => '<red>',
		'[/r]' => '</red>',
		'[l]' => '<blue>',
		'[/l]' => '</blue>',
		'&#8220;' => '"',
		'&#8221;' => '"',
		'&#8222;' => '"',
		'&#8230;' => '...',
		'&#8217;' => '\''
		);
		foreach ($str as $_from => $_to) $this->html = str_replace($_from,$_to,$this->html);

		$pdf=new PDF('P','mm','A4',$this->title,$this->articleurl,false);
		//$pdf->SetCreator("'',''");
		//$pdf->SetDisplayMode('real');
		//$pdf->SetTitle($this->_convert($this->title));
		//$pdf->SetAuthor($this->author);
		$pdf->AddPage();

		// header
		$pdf->PutMainTitle($this->_convert($this->title));
		//$pdf->PutMinorHeading('Article URL');
		//$pdf->PutMinorTitle($this->articleurl,$this->articleurl);
		//$pdf->PutMinorHeading('Author');
		//$pdf->PutMinorTitle($this->_convert($this->author));
		//$pdf->PutMinorHeading("Published: ".@date("F j, Y, g:i a",$this->date));
		//$pdf->PutLine();
		//$pdf->Ln(10);

		// html
		$pdf->WriteHTML($this->_convert(stripslashes($this->html)),$this->L);

		// output
		$pdf->Output($file,'F');

		// stop processing
		//exit;
	}
} 

/************************************/
/* class PDF                        */
/************************************/
class PDF extends FPDF
{
	protected $B;
	protected $I;
	protected $U;
	protected $HREF;
	protected $fontList;
	protected $issetfont;
	protected $issetcolor;

	function __construct($orientation='P',$unit='mm',$format='A4',$_title,$_url,$_debug=false)
	{
		parent::__construct($orientation,$unit,$format);
		$this->B=0;
		$this->I=0;
		$this->U=0;
		$this->HREF='';
		$this->PRE=false;
		$this->SetFont('Arial','',12);
		$this->fontlist=array('Arial');
		$this->issetfont=false;
		$this->issetcolor=false;
		$this->articletitle=$_title;
		$this->articleurl=$_url;
		$this->debug=$_debug;
		$this->AliasNbPages();
	}

	function WriteHTML($html,$bi)
	{
		$this->bi=$bi;
		/*if ($bi)
		$html=strip_tags($html,"<a><img><p><br><font><table><tr><td><blockquote><h1><h2><h3><h4><h5><h6><h7><h6_inner><pre><red><blue><ul><li><hr><b><i><u><strong><em><span><sub><sub1><pLeft10>"); 
		else
			$html=strip_tags($html,"<a><img><p><br><font><table><tr><td><blockquote><h1><h2><h3><h4><h5><h6><h7><h6_inner><pre><red><blue><ul><li><hr><span><sub><sub1><pLeft10>"); 
		
		*/	
		$html=str_replace("\n",' ',$html); //replace carriage returns with spaces
		// debug
		if ($this->debug) { echo $html; exit; }

		$html = str_replace('&trade;','',$html);
		$html = str_replace('&copy;','©',$html);
		$html = str_replace('&euro;','',$html);

		$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
		$skip=false;
		foreach($a as $i=>$e)
		{
			if (!$skip) {
				if($this->HREF)
					$e=str_replace("\n","",str_replace("\r","",$e));
				if($i%2==0)
				{
					// new line
					if($this->PRE)
						$e=str_replace("\r","\n",$e);
					else
						$e=str_replace("\r","",$e);
					//Text
					if($this->HREF) {
						$this->PutLink($this->HREF,$e);
						$skip=true;
					} elseif($this->ALIGN=='center'){
	                    $this->Cell(0,5,$e,0,1,'C');
					}else 
						$this->Write(5,stripslashes(txtentities($e)));
                    /*elseif($this->tdbegin) {
				        if(trim($e)!='' && $e!="&nbsp;") {
				            $this->Cell($this->tdwidth,$this->tdheight,$e,$this->tableborder,'',$this->tdalign,$this->tdbgcolor);
				        }
				        elseif($e=="&nbsp;") {
				            $this->Cell($this->tdwidth,$this->tdheight,'',$this->tableborder,'',$this->tdalign,$this->tdbgcolor);
				        }
				    }*/
					 
				} else {
					//Tag
					if (substr(trim($e),0,1)=='/')
						$this->CloseTag(strtoupper(substr($e,strpos($e,'/'))));
					else {
						//Extract attributes
						$a2=explode(' ',$e);
						$tag=strtoupper(array_shift($a2));
						$attr=array();
						foreach($a2 as $v) {
							if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
								$attr[strtoupper($a3[1])]=$a3[2];
						}
						$this->OpenTag($tag,$attr);
					}
				}
			} else {
				$this->HREF='';
				$skip=false;
			}
		}

	}

	function OpenTag($tag,$attr)
	{
		//Opening tag
		switch($tag){
	
			case 'H1':
				$this->Ln(4);
				$this->SetTextColor(150,0,0);
				$this->SetFontSize(22);
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'H2':
				$this->Ln(5);
				$this->SetFontSize(18);
				$this->SetStyle('U',true);
				$this->ALIGN=$attr['ALIGN'];
				$this->SetTextColor(0,0,0);	
				break;
			case 'H3':
				$this->Ln(12);
				$this->SetFont('Arial','',25);
				$this->SetFontSize(25);
				$this->SetStyle('U',true);
				$this->SetStyle('B',true);
				$this->ALIGN=$attr['ALIGN'];
				$this->SetTextColor(0,0,0);	
				break;
			case 'H4':
				$this->Ln(10);
				$this->SetFont('Arial','',13);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(13);
				$this->SetStyle('B',true);
				$this->SetStyle('U',false);	
				$this->SetStyle('I',false);	
				break;
			case 'H5':
				$this->Ln(10);
				$this->SetFont('Arial','',16);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(16);
				$this->SetStyle('B',true);
				$this->SetStyle('U',false);	
				$this->SetStyle('I',false);	
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'H6':
				$this->Ln(10);
				$this->SetFont('Arial','',12.8);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(12.8);
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'H7':
				$this->Ln(1);
				$this->SetFont('Arial','',12.8);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(12.8);
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'H6_INNER':
				$this->Ln(10);
				$this->SetFont('Arial','',12.8);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(12.8);
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'PARA':
				$this->Ln(10);
				$this->SetFont('Arial','',12);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(12);
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'BOLD':
				$this->Ln(10);
				$this->SetFont('Arial','B',12);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(12);
				$this->SetStyle($tag,true);
				break;			
			case 'PRE':
				$this->SetFont('Courier','',11);
				$this->SetFontSize(11);
				$this->SetStyle('B',false);
				$this->SetStyle('I',false);
				$this->PRE=true;
				break;
			case 'RED':
				$this->SetTextColor(255,0,0);
				break;
			case 'BLOCKQUOTE':
				$this->mySetTextColor(100,0,45);
				$this->Ln(3);
				break;
			case 'BLUE':
				$this->SetTextColor(0,0,255);
				break;
			case 'I':
			case 'EM':
				if ($this->bi)
					$this->SetStyle('I',true);
				break;
			case 'U':
				$this->SetStyle('U',true);
				break;
			case 'A':
				$this->HREF=$attr['HREF'];
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'IMG':
				if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
					if(!isset($attr['WIDTH']))
						$attr['WIDTH'] = 0;
					if(!isset($attr['HEIGHT']))
						$attr['HEIGHT'] = 0;
					$this->Image($attr['SRC'], $this->GetX(), $this->GetY()+1, px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
				}
				break;
			case 'LI':
				$this->Ln(7);
				$this->SetFont('Arial','',12.8);
				$this->SetTextColor(0,0,0);
				$this->Write(5,'');
				$this->mySetTextColor(-1);
					$this->SetStyle('B',false);
					$this->SetStyle('U',false);	
					$this->SetStyle('I',false);	
				$this->ALIGN=$attr['ALIGN'];
				break;			
			 case 'TABLE': // TABLE-BEGIN
	            if( !empty($attr['BORDER']) ) {$this->tableborder=$attr['BORDER'];} else {$this->tableborder=0;} 
	         break;	
			case 'TR':
				$this->Ln(10);
				$this->Write(1,'');	
				$this->ALIGN=$attr['ALIGN'];
				break;
			case 'TD':
				$this->SetFont('Arial','',12.8);
				$this->SetTextColor(0,0,0);
				$this->SetFontSize(12.8);
				if( !empty($attr['WIDTH']) ) $this->tdwidth=($attr['WIDTH']/4);
			        else $this->tdwidth=300; // Set to your own width if you need bigger fixed cells
		        if( !empty($attr['HEIGHT']) ) $this->tdheight=($attr['HEIGHT']/6);
		        else $this->tdheight=6; // Set to your own height if you need bigger fixed cells
		        if( !empty($attr['ALIGN']) ) {
		            $align=$attr['ALIGN'];        
		            if($align=='LEFT') $this->tdalign='L';
		            if($align=='CENTER') $this->tdalign='C';
		            if($align=='RIGHT') $this->tdalign='R';
		        }
		        else $this->tdalign='L'; // Set to your own
				if( !empty($attr['BGCOLOR']) ) {
					$coul=hex2dec($attr['BGCOLOR']);
					$this->SetFillColor($coul['R'],$coul['G'],$coul['B']);
					$this->tdbgcolor=true;
				}
				$this->tdbegin=true;
				break;
			case 'BR':
				$this->Ln(5);
				break;
			case 'P':
				$this->Ln(5);
				break;
			case 'HR':
				$this->PutLine();
				break;
			case 'SPAN':
					$this->SetTextColor(255,0,0);
				break;
			case 'SUB':
					$this->SetTextColor(0,0,0);
					
				break;
			case 'SUB1':
					$this->SetTextColor(0,0,0);
					$this->SetFontSize(8);
				break;
			case 'PLEFT10':
					$this->Write(4,'');
					$this->SetTextColor(255,255,255);
				break;
            
            
            case 'PLEFT':
					$this->Write(4,'');
            break;
            
            case 'PRIGHT':
					$this->Write(150,'');
            $this->SetTextColor(0,0,0);
            break;


			case 'LEFTIMAGE':
					$this->Write(190,'');
					//$this->SetTextColor(255,255,255);
					
				break;
			case 'FONT':
				if (isset($attr['COLOR']) && $attr['COLOR']!='') {
					$coul=hex2dec($attr['COLOR']);
					$this->mySetTextColor($coul['R'],$coul['G'],$coul['B']);
					$this->issetcolor=true;
				}
				if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
					$this->SetFont(strtolower($attr['FACE']));
					$this->issetfont=true;
				}
				break;
		}
	}

	function CloseTag($tag)
	{
		//Closing tag
		if ($tag=='H1' || $tag=='H2' || $tag=='H3' || $tag=='H4'){
			$this->Ln(6);
			$this->SetFont('Times','',12);
			$this->SetFontSize(12);
			$this->SetStyle('U',false);
			$this->SetStyle('B',false);
			$this->mySetTextColor(-1);
		}
		if ($tag=='BOLD'){
			$this->SetFont('Arial','B',12);
			$this->SetTextColor(0,0,0);
			$this->SetFontSize(12);
			$this->SetStyle($tag,false);
		}
		if ($tag=='PRE'){
			$this->SetFont('Times','',12);
			$this->SetFontSize(12);
			$this->PRE=false;
		}
		if ($tag=='RED' || $tag=='BLUE')
			$this->mySetTextColor(-1);
		if ($tag=='BLOCKQUOTE'){
			$this->mySetTextColor(0,0,0);
			$this->Ln(3);
		}
		if($tag=='STRONG')
			$tag='B';
		if($tag=='EM')
			$tag='I';
		if((!$this->bi) && $tag=='B')
			$tag='U';
		if($tag=='B' || $tag=='I' || $tag=='U')
			$this->SetStyle($tag,false);
		if($tag=='A')
			$this->HREF='';
		if($tag=='FONT'){
			if ($this->issetcolor==true) {
				$this->SetTextColor(0,0,0);
			}
			if ($this->issetfont) {
				$this->SetFont('Times','',12);
				$this->issetfont=false;
			}
		}
		if($tag=='TD') { // TD-END
		    $this->tdbegin=false;
		    $this->tdwidth=0;
		    $this->tdheight=0;
		    $this->tdalign="L";
		    $this->tdbgcolor=false;
		}
		if($tag=='TR') { // TR-END
		    $this->Ln();
		}
		if($tag=='TABLE') { // TABLE-END
		    $this->tableborder=0;
		}
	}

	function Footer()
	{
		//Go to 1.5 cm from bottom
		$this->SetY(-15);
		//Select Arial italic 8
		$this->SetFont('Times','',8);
		//Print centered page number
		$this->SetTextColor(0,0,0);
		$this->Cell(0,4,'Page '.$this->PageNo().'/{nb}',0,1,'C');
		$this->SetTextColor(0,0,180);
		//$this->Cell(0,4,'ASM Personal Consultation Report');
		$this->mySetTextColor(-1);
	}

	function Header()
	{
		//Select Arial bold 15
		$this->SetTextColor(0,0,0);
		$this->SetFont('','',10);
		//$this->Cell(0,10,$this->articletitle,0,0,'C');
		$this->Ln(4);
		$this->Cell(0,10,$this->articleurl,0,0,'C');
		$this->Ln(7);
		//Line break
		$this->Ln(12);
		$this->SetFont('','',12);
		$this->mySetTextColor(-1);
	}

	function SetStyle($tag,$enable)
	{
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		foreach(array('B','I','U') as $s) {
			if($this->$s>0)
				$style.=$s;
		}
		$this->SetFont('',$style);
	}

	function PutLink($URL,$txt)
	{
		//Put a hyperlink
		$this->SetTextColor(0,0,255);
		$this->SetStyle('U',true);
		$this->Write(5,$txt,$URL);
		$this->SetStyle('U',false);
		$this->mySetTextColor(-1);
	}

	function PutLine()
	{
		$this->Ln(2);
		$this->Line($this->GetX(),$this->GetY(),$this->GetX()+187,$this->GetY());
		$this->Ln(3);
	}

	function mySetTextColor($r,$g=0,$b=0){
		static $_r=0, $_g=0, $_b=0;

		if ($r==-1) 
			$this->SetTextColor($_r,$_g,$_b);
		else {
			$this->SetTextColor($r,$g,$b);
			$_r=$r;
			$_g=$g;
			$_b=$b;
		}
	}

	function PutMainTitle($title) {
		if (strlen($title)>55)
			$title=substr($title,0,55)."...";
		$this->SetTextColor(0,0,0);
		$this->SetFontSize(27);
		$this->SetStyle('B',true);
		$this->SetStyle('U',true);
		$this->SetFillColor(255,255,255);
		$this->Cell(0,0,$title,0,1,"l",1);
		$this->SetFillColor(255,255,255);
		$this->SetFontSize(27);
		$this->bi= false;
	}

	function PutMinorHeading($title) {
		$this->SetFontSize(12);
		$this->Cell(0,5,$title,0,1,"C");
		$this->SetFontSize(12);
	}

	function PutMinorTitle($title,$url='') {
		$title=str_replace('http://','',$title);
		if (strlen($title)>70)
			if (!(strrpos($title,'/')==false))
				$title=substr($title,strrpos($title,'/')+1);
		$title=substr($title,0,70);
		$this->SetFontSize(16);
		if ($url!='') {
			$this->SetStyle('U',false);
			$this->SetTextColor(0,0,180);
			$this->Cell(0,6,$title,0,1,"C",0,$url);
			$this->SetTextColor(0,0,0);
			$this->SetStyle('U',false);
		} else
			$this->Cell(0,6,$title,0,1,"C",0);
		$this->SetFontSize(12);
		$this->Ln(4);
	}
} // class PDF

?>
