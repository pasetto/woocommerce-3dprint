<?php
// $postId = 6151;
$postId = 6154;

// echo Woo3Dprint_Slicer_Fdm::slice($postId);
$media = get_post_meta($postId, 'arquivo_orcado', true);

$upload_wp_info = wp_upload_dir();

$file_post = get_post($media);
$location_file['id'] = $file_post->ID;
$location_file['filename'] = array_slice(explode('/',$file_post->guid),-1,1)[0];
$location_file['url'] = $file_post->guid;
$location_file['path'] = str_replace($upload_wp_info['baseurl'], $upload_wp_info['basedir'], $file_post->guid);

$print_args = array(
    "layerHeight"   => 0.5,
    "infillPercentage"  => 100,
    "printSpeed"    => 30,
    "pricePerHour"  => 2,
    "pricePerGram"  => 0.2,
    "material"  => 'PLA',
);

if (file_exists( $location_file['path'] ) == false)
    trigger_error("STL file not found at ". $location_file['path'] , E_USER_ERROR);
if (is_file( $location_file['path'] ) == false)
    trigger_error("STL path is not a file, ". $location_file['path'] , E_USER_ERROR);
if ($print_args['infillPercentage'] < 0 || $print_args['infillPercentage'] > 100)
    trigger_error("Infill percentage out of bounds: ".$print_args['infillPercentage'].", (0 to 100) ". $location_file['path'] , E_USER_ERROR);

$printTime = $filamentUsed = 0;
$args = array();
$output = array();

/**
 * Ativar depois
 */
// $ch = curl_init(self::KILL_FROZEN_SLICERS_URL);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HEADER, false);
// curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100);
// curl_exec($ch);

$args[] = "slice ";
$args[] = "-v";
$args[] = "-j D:/xampp/htdocs/producao/nanodesign.com.br/wp-content/plugins/woocommerce-3dprint/_inc/fdm-configs/profiles/prusa_i3/prusa_i3.def.json";
$args[] = "-s layerThickness=".($print_args['layerHeight']*1000);

$args[] = "-s sparseInfillLineDistance=".(100*($print_args['layerHeight']*1000)/$print_args['infillPercentage']);
$args[] = "-s printSpeed=".$print_args['printSpeed'];
$args[] = "-s infillSpeed=".$print_args['printSpeed'];
$args[] = "-s filamentDiameter=".(1.75*1000);
$args[] = "-o ". $location_file['path'] .".gcode ". $location_file['path'];

$command =   "D:\Program Files\Ultimaker Cura 4.12.1\CuraEngine.exe ".implode(" ", $args);
exec($command, $output, $return);
echo "<pre>".print_r($output,true)."</pre>";
$output["slicerCommand"] = array("command"=>$command,"returnCode"=>$return,"commandOutput"=>$output);
print_r($output);
// foreach($output as $line)
//     if (substr($line, 0, 4) == "Fail")
//         $output['error'] = $line;
//     else if (substr($line, 0, 12) == "Print time: ")
//         $printTime = substr($line, 12);
//     else if (substr($line, 0, 10) == "Filament: ")
//         $filamentUsed = substr($line, 10);



// "D:\Program Files\Ultimaker Cura 4.12.1\CuraEngine.exe" 
// slice -v 
// -j "D:\xampp\htdocs\producao\nanodesign.com.br\wp-content\plugins\woocommerce-3dprint\_inc\fdm-configs\profiles\prusa_i3\prusa_i3.def.json"
// -o "D:\xampp\htdocs\producao\nanodesign.com.br\wp-content\plugins\woocommerce-3dprint\export\headsetholderV2-1.stl.gcode"
// -l "D:\xampp\htdocs\producao\nanodesign.com.br/wp-content/uploads/2021/12/headsetholderV2-1.stl"
// -p
