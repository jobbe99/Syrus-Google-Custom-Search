<?php

/*
Plugin Name: Syrus Google Custom Search
Description: This plugin works with GCS for elaborate a maximum of 5 research results to append to wordpress searchform
Version: 1.0
Author: Syrus Industry
Author URI: http://www.syrusindustry.com
License: GPL2
*/


register_activation_hook( __FILE__, 'activeScript' );

function activeScript(){?>
    <script type="text/javascript">
    jQuery("form[role=search]").on('submit', function () {
        setTimeout(function() {
            var countPosts = jQuery(".type-page").length +  jQuery(".type-post").length;
            var searchTerms = jQuery(this).find("input[type=text]").val();
            if(countPosts < 2){
                jQuery.ajax({
                    type: "POST",
                    url: "/Syrus-Google-Custom-Search.php",
                    data: {
                        action: 'sites',
                        data: {findedResults: ((countPosts == 0) ? 5 : 4) , researchTerms: searchTerms}
                    },
                    success: function (response) {
                        jQuery(this).after(response["res"]);
                    }
                });
            }
        }, 700);
    });
    </script> <?php
}

add_action( 'wp_ajax_sites','sites' );
add_action( 'wp_ajax_nopriv_sites', 'sites' );

define( 'REL_SRC', '1' );

function sites(){
    if (!defined(REL_SRC)) {
        echo json_encode(array("res" => "Non hai i permessi per eseguire questa operazione"));
    }else {
        if (isset($_POST['researchTerms']) && isset($_POST['findedResults'])) {
            $researchTerms = $_POST['researchTerms'];
            $showLimitRes = intval($_POST['findedResults']);
            $contLimit = 1;

            /*CHIAVE API CUSTOM SEARCH E ID*/

            $queryString = str_replace(" ", "+", trim($researchTerms));

            //qui è possibile cambiare key apis e cx del motore personalizzato
            $google_results = file_get_contents("https://www.googleapis.com/customsearch/v1?key=AIzaSyAO-71qPhKd1HoTlWzkbkhYcgYGeaeCVVk&cx=004203485068595057873:huz8xk5w4t8&num=5&q='$queryString';");

            $readbleResults = json_decode($google_results, true);

            //costruzione della risposta HTML

            $response = "";

            foreach ($readbleResults["items"] as $relatedSearch) {
                if ($contLimit > $showLimitRes) break;

                $relatedElement = '<div class="page type-page status-publish">';
                //$relatedElement .= '<p class="date-author">' . $dataPubblicazione . '</p>';
                $relatedElement .= ('<h3 class="teaser-title"><a href="'. $relatedSearch["link"] .'" rel="bookmark">'
                    . $relatedSearch["title"]  . '</a></h3>' );
                $relatedElement .= '<div class="teaser-text">';
                $relatedElement .= ('<p>' . $relatedSearch["snippet"] . '</p>');
                $relatedElement .= ('<p><a href="'. $relatedSearch["link"] .'">' . "Continue Reading » " . '</a></p>');
                $relatedElement .= '</div></div>';
                $response .= $relatedElement;

                $contLimit++;
            }
            echo json_encode(array("res" => $response));
        }
    }
}