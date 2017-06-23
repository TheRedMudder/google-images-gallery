<?php
include 'config.php';
$sub_folder = '';
if (isset($_GET['folder'])) {
    $sub_folder = $_GET['folder'];
    $folder_url = explode('/', $sub_folder);
    $url_added = "";
    $photo_dirs = "";
    foreach ($folder_url as $f_url) {
        $url_added .= $f_url;
        $photo_dirs .= "<a class='flink' href='gallery.php?folder=" . $url_added . "'>" . $f_url . "</a> &rarr; ";
        $url_added .= '/';
    }
    $files = scandir($img_folder . '/' . $sub_folder);
} else {
    $files = scandir($img_folder);
}


$files = array_slice($files, 2);
//    shuffle($files);
$files = array_slice($files, ( isset($_GET['json']) ? 25 * $_GET['json'] : 0), 25);

$pics = array();
$folders = array();
foreach ($files as $f) {
    $p = array();

    $p['src'] = ((empty($sub_folder)) ? $f : $sub_folder . '/' . $f);
    $p['id'] = preg_replace('/[^\d]/', '', $f);

    $info = getimagesize($img_folder . '/' . $p['src']);
    $p['w'] = $info[0];
    $p['h'] = $info[1];
    //Check to make sure file is not a folder
    if (!is_dir($img_folder . '/' . $p['src'])) {
        //Check to make sure allowed file type
        if (in_array(strtolower(pathinfo($p['src'])['extension']), $img_types)) {
            $pics[] = $p;
        }
    } else {
        $d = array("name" => htmlentities($f), "src" => $p['src'], 'count' => count(scandir($img_folder . "/" . $p['src'])) - 2);
        $folders[] = $d;
    }
}
if (isset($_GET['json'])) {
    $jsonout = array("picjson" => $pics, "folderjson" => $folders);
    print_r(json_encode($jsonout));
    die();
}
//    
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <link rel="apple-touch-icon" href="iconiphone.png" sizes="100x100">
        <meta charset="UTF-8" />
        <title>Photo Gallery</title>
        <style type="text/css">
            body {
                margin: 0;
                padding: 0;
            }

            h1 {
                font: bold 2.5em "Helvetica Neue", Helvetica, Arial, sans-serif;
                text-align: center;
                border-bottom: 1px solid #666;
                padding: 16px 0;
                margin: 16px 0 32px;
            }

            #container,#folder-container  {
                width: 100%;
                margin: 0 auto;
            }

            .row {
                position: relative;
            }

            .row:after {
                content: '.';
                font-size: 0;
                height: 0;
                visibility: hidden;
                display: block;
                clear: both;
            }

            .row-info {
                position: absolute;
                left: 0;
                bottom: 0;
            }

            .pic {
                float: left;
                margin: 2px;
                border: 1px solid #fff;
                text-align: center;
                -webkit-transition: box-shadow 250ms;
                position: relative;
                z-index: 0;
            }

            .pic-container {
                width: 100%;
                height: 100%;
                background: #000;
                display: none;
            }

            .pic-container img {
                opacity: 0.85;
                -webkit-transition: opacity 250ms;
            }

            .pic-hover:hover {
                box-shadow: 0 0 32px 4px rgba(0, 0, 0, 0.9);
                z-index: 1;
                cursor: pointer;
            }

            .pic-hover:hover img {
                opacity: 1;
            }

            .folder {
                background-color: #ededed;
                position: relative;
                border: none;
                color: #5a5b5c;
                padding: 15px 15px 15px 100px;
                text-align: left;
                text-decoration: none;
                font-size: 16px;
                margin: 8px 4px;
                cursor: pointer;
                border-radius: 8px;
                min-height:60px;
            }
            @media screen and (min-width: 960px) {
                .folder{
                    display: inline-block;
                    width:200px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #container,#folder-container  {
                    width: 1000px;
                }
            }
            .folder .folder-img {
                position: absolute;
                top:0px;
                left: 0px;
                width:200px;
                height:100px;
                padding:0px;margin:0px;
                background:url(data:image/png;base64,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)
                    no-repeat
                    left center;

            }
            a.flink, a.flink:link, a.flink:visited, a.flink:hover, a.flink:focus, a.flink:active{
                color:black;
            }


        </style>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js" type="text/javascript"></script>
        <script type="text/javascript">
            var isloading = true;
            var default_folder_container;
            var default_photo_container;

            var timeout;
            var page = 0;
            var diswidth = screen.width;
            var rowWidth = Math.min(1000, diswidth);
            var store_folder;
            var store_images;
            function escapeSQ(inp) {
                var strwithqoute = inp;
                strwoquote = strwithqoute.replace(/'/g, "\\'");
                return strwoquote;
            }
            function updateLayout() {
                isloading = true;
                document.title = "Loading - Photo Gallery";
                $('#folder-container').html(default_folder_container);
                $('#container').html(default_photo_container);
                if (Math.abs(window.orientation) === 90) {
                    // Landscape
                    rowWidth=screen.height;
                } else {
                    //Potrait
                    rowWidth=screen.width;
                }
                displaypic(store_images);
                displayfolder(store_folder);
            }
            window.onorientationchange = updateLayout;
            window.onbeforeunload = function () {
                window.scrollTo(0, 0);
            }
            $(document).ready(function () {
                $(this).scrollTop(0);
                default_folder_container = $('#folder-container').html();
                default_photo_container = $('#container').html();

                var pics = <?= json_encode($pics) ?>;
                var folders = <?= json_encode($folders) ?>;
                store_folder = folders;
                store_images = pics;
                displaypic(pics);
                displayfolder(folders);

                $(window).scroll(function () {
                    if ($(window).scrollTop() + $(window).height() > getDocHeight() - 20) {
                        if (!isloading) {

                            page++;
                            document.title = "Loading - Photo Gallery";
                            isloading = true;

                            $.getJSON('./gallery.php?<?php echo ( isset($_GET['folder']) ? 'folder=' . addslashes($_GET['folder']) . '&' : '' ); ?>json=' + page, function (data) {
                                store_images = store_images.concat(data.picjson);
                                store_folder = store_folder.concat(data.folderjson);
                                displaypic(data.picjson);
                                displayfolder(data.folderjson);
                            });

                        } else {

                            document.title = "No More Content - Photo Gallery";
                        }


                    }
                });
            });
            function getDocHeight() {
                var D = document;
                return Math.max(
                        D.body.scrollHeight, D.documentElement.scrollHeight,
                        D.body.offsetHeight, D.documentElement.offsetHeight,
                        D.body.clientHeight, D.documentElement.clientHeight
                        );
            }
            function displayfolder(folders) {
                var a = 0;
                var folderhtml = '';
                while (a < folders.length) {
                    folderhtml += foldbuild(folders[a]);
                    a++;
                }
                $("#folder-container").append(folderhtml);

            }
            function foldbuild(folder) {
                return ('<div class="folder" onclick="goto(\'' + escapeSQ(folder.src) + '\')"><div class="folder-img" style="align: left;"></div><b>' + folder.name + '</b><br/>Images:' + folder.count + '<br/></div>');
            }
            function displaypic(pics) {
                clearTimeout(timeout);
                var spacing = 6;
                var b = 0, e = 0, rowHeight;
                while (e < pics.length) {
                    do {
                        e++;

                        var totalWidth = 0;
                        for (var i = b; i < e; i++)
                            totalWidth += pics[i].w / pics[i].h;

                        rowHeight = Math.round((rowWidth - spacing * (e - b)) / totalWidth);
                    } while (rowHeight > 200 && e < pics.length);

                    var actualRowWidth = 0;
                    for (var i = b; i < e; i++) {
                        pics[i]._w = Math.round(pics[i].w / pics[i].h * rowHeight);
                        pics[i]._h = rowHeight;
                        actualRowWidth += pics[i]._w;
                    }

                    var diff = (rowWidth - spacing * (e - b)) - actualRowWidth;
                    var per = Math.floor(diff / (e - b));
                    var extra = diff % (e - b);
                    if (extra < 0)
                        extra += e - b;
                    for (var i = b; i < e; i++) {
                        pics[i]._w += per;
                        if (i - b < extra)
                            pics[i]._w++;
                    }

                    var rowDiv = $('<div></div>')
                            .addClass('row')
                            .appendTo('#container');

                    for (var i = b; i < e; i++) {
                        var pic = $('<div></div>')
                                .addClass('pic')
                                .css({width: pics[i]._w, height: pics[i]._h})
                                .appendTo(rowDiv);

                        var picContainer = $('<div></div>')
                                .addClass('pic-container');

                        var img = $(buildimg(pics[i]))
                                .load(function () {
                                    $(this).parents('.pic-container').fadeIn(800, function () {
                                        $(this).parent().addClass('pic-hover');
                                    });
                                });

                        picContainer
                                .html(img)
                                .wrapInner('<a href="pics/' + pics[i].src + '" />');

                        pic.append(picContainer).appendTo(rowDiv);
                    }

                    b = e;
                }
                timeout = setTimeout(function ()
                {
                    isloading = false;
                    document.title = "Done - Photo Gallery";
                }, 500);
            }
            function buildimg(pic) {
                return (
                        '<img ' +
                        'src="pic/' + pic._w + 'x' + pic._h + '/' + pic.src + '" ' +
                        'width="' + pic._w + '" ' +
                        'height="' + pic._h + '" ' +
                        '/>');
            }
            function goto(folder) {
                document.location = 'gallery.php?folder=' + folder;
            }
            
        </script>
    </head>
    <body>
        <div id="folder-container">

            <h2><a class="flink" href="gallery.php">Files</a> &rarr; <?php echo $photo_dirs; ?> </h2>

        </div>

        <div id="container">

            <h1>Photo Gallery</h1>
        </div>
    </body>
</html>
