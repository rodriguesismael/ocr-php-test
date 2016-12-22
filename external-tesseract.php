<?php
 //"solarium/solarium": "^3.6",
require "vendor/autoload.php";
$pathToFiles = "/var/www/html/intranet_uploads/";

function limparImagens($path){
	$arrayImagens = glob($path.'*.jpg');
	foreach ($arrayImagens as $remover) {
		unlink($remover);
	}
}

function converterImagem($path,$file,$completo=false){
        $filename   = pathinfo($path.$file,PATHINFO_FILENAME);
        $outputFile = $filename."-%d.jpg";
        system("gm convert -density 400 $path$file +adjoin -depth 8 $path$outputFile");
        $arrayFiles = glob($path.$filename.'-*.jpg');
        if($completo){
        	$filelist = implode(" ", $arrayFiles);
        }else{
	        $tamanho = count($arrayFiles);
	        $filelist = $arrayFiles[0];
	        if($tamanho > 1){
	            //o OCR será feito apenas com a primeira e ultima páginas
	            $filelist.= " ".$arrayFiles[$tamanho-1];
	        }
    	}
        $mergedFile = $filename."Merged.jpg";
        system("gm convert -density 400 $filelist -append -depth 8 $path$mergedFile");
        return $mergedFile;
}


if(isset($_GET['file'])){
	$init = microtime(true);
	$arquivo = $_GET['file'];
	if(isset($_GET['completo']) && $_GET['completo'] == 1){
		$completo = true;
		$content = "todo o conteudo";
	}else{
		$completo = false;
		$content = "duas paginas";
	}
	$imgOCR = converterImagem($pathToFiles, $arquivo.".pdf",$completo);
	echo "<h3>Exibindo o texto extraido de $content do arquivo $arquivo.pdf</h3>";
	echo (new TesseractOCR($pathToFiles.$imgOCR))
	 	->lang('por')
	    ->run();
	$fim = microtime(true);

	printf("<br> <h3>O tempo para processar a requisção foi %.1f segundos</h3>",($fim-$init)); 
	
	echo "[<a href='external-tesseract.php'>Voltar</a>]";
	limparImagens($pathToFiles);
}else{
	echo "<h2>Escolha uma opção para gerar o OCR do arquivo correspondente</h2>";
	$arrayFiles = glob($pathToFiles."*.pdf");
	foreach ($arrayFiles as $arquivo) {
		$filename = pathinfo($arquivo, PATHINFO_FILENAME);
		echo "$filename [<a href='?file=$filename&completo=1'> arquivo completo</a>] [<a href='?file=$filename'> primeira e ultima paginas</a>]</br>";
	}		
}

?>