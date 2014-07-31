<?php
/**
 *
 * XMLExcel Class file
 *
 * Simple excel generating from PHP5
 * reference opensource.org/licenses/mit-license.php
 *
 *
 * @author breeze <wfwq2008#gmail.com>
 * @category protected.extentions.xmlexcel
 * @package Extensions
 * @license breeze
 * @version 1.0
 */

/**
 * generate XML to export excel
 *
 * 
 */
class XMLExcel{

    public $workSheet = array();
    public $encoding = 'utf-8';
    public $styles = '';
    /**
     * header of html to export excel
     * @var string
     */
    private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<?mso-application progid=\"Excel.Sheet\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"\n xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
    /**
     * Footer (of document)
     * @var string
     */
    private $footer = "</Workbook>";
    
    private $sheets = "";

    public function getSheet($index = 0){
        if ( !isset($this->workSheet[$index])){
            $this->workSheet[$index] = new CreateExcel();
        }
        return $this->workSheet[$index];
    }
    /*
     * set default styles
     */
    public function setDefaultStyles($font='Arial',$size='12',$vertical='center'){
        $vertical = ucfirst($vertical);
        $this->styles = "\n<Styles>
<Style ss:ID=\"Default\" ss:Name=\"Normal\">
<Alignment ss:Vertical=\"{$vertical}\"/>
<Borders/>
<Font ss:FontName=\"{$font}\" x:CharSet=\"134\" ss:Size=\"{$size}\" ss:Color=\"#000000\"/>
<Interior/>
<NumberFormat/>
<Protection/>
</Style>
</Styles>
";
    }
    public function generateXMLs($name){
        
        header("Content-Type: application/vnd.ms-excel; charset=" . $this->encoding);
                header("Content-Disposition: attachment; filename=\"" . $name . "\"");

                $this->sheets =  stripslashes (sprintf($this->header, $this->encoding));
                $this->sheets  .= $this->styles;
        foreach($this->workSheet as $sheet){
            $this->sheets .=$sheet->generateXML();
        }
        $this->sheets .= $this->footer;
        echo $this->sheets;
    }
}
class CreateExcel{

    private $sheetTitle ;
    private $columns = array();
    private $units = array();

    private $defaultHeight;
    private $defaultWidth;

    private $height = array();
    
    /*
     *  set Sheet Title
     */
    public function setSheetTitle($name){
        $this->sheetTitle = $name;
    }

    /*
     * set default height
     * @return void
     */
    public function setDefaultHeight($h){
        $this->defaultHeight = "ss:DefaultRowHeight=\"{$h}\"";
    }

    /*
     * set default width
     */
    public function setDefaultWidth($w){
        $this->defaultWidth = "ss:DefaultColumnWidth=\"{$w}\"";
    }
    /*
     * set height
     * @param $cell 1,2,3
     * @param $height
     * @return void
     */
    public function setHeight($cell,$height){
        $this->height[$cell] = "ss:Height=\"{$height}\"";
    }

    /*
     * @columns  string "A:100,B:200"
     *
     */
    public function setWidths($columns){
        $columns = explode(",",$columns);
        foreach($columns as $column){
            $value = explode(':',$column);
            $this->columns[$this->_toNumber($value[0])]=$value[1];
        }
    }

    public function setwidth($cell,$width){
        $this->columns[$cell]=$width;
    }

    /*
     * @param string "A3:D5"|"A3:D5,B3:E4"
     * @return void
     */
    public function setMerges($merges){
        $merges = explode(",",$merges);
        foreach($merges as $merge){
            $this->setMerge($merge);
        }
        return $this;
    }

    public function setMerge($merge){
        $merge = explode(":",$merge);
        $cell1 = substr($merge[0],0,1);
        $row1  = substr($merge[0],1);
        $cell2 = substr($merge[1],0,1);
        $row2 = substr($merge[1],1);

        if($cell1.$row2 != $merge[0]) {
            $this->setMergeDown($merge[0].':'.$cell1.$row2);
        }
        if($cell2.$row1 != $merge[0]){
            $this->setMergeAcross($merge[0].':'.$cell2.$row1);
        }
        return $this;
    }

    /*
     * @param string "A3:A5"|A3:A5,B3:B5
     * @return void
     */
    public function setMergeDowns($mergeDowns){
        $mergeDowns = explode(",",$mergeDowns);
        foreach($mergeDowns as $mergeDown){
            $this->setMergeDown($mergeDown);
        }
        return $this;
    }

    public function setMergeDown($mergeDown){
        $units = explode(":",$mergeDown);
        if($units[0][0] != $units[1][0]){

        }

        $cell = $this->_toNumber($units[0][0]);
        $row = substr($units[0],1);
        $md = substr($units[1],1) - substr($units[0],1);
        if ($md <= 0){

        }
        $this->units[$row][$cell] = array('mergeDown'=>$md);
        return $this;
    }

    /**
     * @param string "A3:D3"
     * @return void
     */
    public function setMergeAcrosses($mergeAcrosses){
        $mergeAcrosses = explode(",",$mergeAcrosses);
        foreach($mergeAcrosses as $mergeAcross){
            $this->setMergeAcross($mergeAcross);
        }
        return $this;
    }

    public function setMergeAcross($mergeAcross){
        $units = explode(":",$mergeAcross);
        if(substr($units[0],1) != substr($units[1],1)){

        }
        
        $row = $this->_toNumber($units[0][0]);
        $cell = substr($units[0],1);

        $ma = $this->_toNumber($units[1][0]) - $this->_toNumber($units[0][0]);
        if ($ma <= 0){

        }
        $this->units[$row][$cell] = array('mergeAcross'=>$ma);

        return $this;
    }
    /**
     *@param $cell string A1
     *@param $value string 'value'
     */
    public function setCellValue($cell,$value){
        $row = substr($cell,1);
        $cell = $this->_toNumber($cell[0]);
        $this->units[$row][$cell] = array('data'=>$value);
    }

    private  function _setColumns(){
        $columns = '';
        foreach($this->columns as $k=>$v){
            $columns .= "\n<Column ss:Index=\"".$k."\" ss:AutoFitWidth=\"0\" ss:Width=\"{$v}\"/>";
        }
        return $columns;
    }
    /*
     * @param $letter string
     */
    private function _toNumber($letter){
        return ord($letter) - 64;
    }

    /*
     * Generate Sheet
     */
    public function generateXML(){
        $lines = array();
        $sheetTitle = "\n<Worksheet ss:Name=\"" . $this->sheetTitle . "\">\n<Table {$this->defaultHeight} {$this->defaultWidth}>\n";
        array_push($lines,$sheetTitle);
        array_push($lines,$this->_setColumns());
        
        foreach($this->units as $row => $unit){
            $height = isset($this->height[$row])?$this->height[$row]:'';
            $line = "\n<Row ss:Index=\"{$row}\" {$height} >";
            foreach($unit as $cell=> $v){
                $mergeDown = isset($v['mergeDown']) ? 'ss:MergeDown="'.$v['mergeDown'].'"':'';
                $mergeAcross = isset($v['mergeAcross']) ? 'ss:MergeAcross="'.$v['mergeAcross'].'"':'';
                $data = isset($v['data']) ? $v['data']: '';
                $line .= "\n<Cell ss:Index=\"{$cell}\"  {$mergeAcross} {$mergeDown}><Data ss:Type=\"String\">{$data}</Data></Cell>";

            }
            $line .= "\n</Row>";
            array_push($lines,$line);
        }

        array_push($lines,"\n</Table>\n</Worksheet>\n");
        return join("",$lines);
    }

    private function displayError($error){
        echo $error;
        exit;
    }
}