<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_register_controller.php');

/** 動画を見たら、オンデマンド配信イベントは参加済みにする */
$user_id = $_SESSION['user_id'] ?? null;
// URLから "file" パラメータを取得
$file = isset($_GET['file']) ? $_GET['file'] : null;
if ($file) {
    // ファイルパスを分割して、materialCourseIdを取得
    $pathParts = explode('/', $file); // '/'で分割
    $materialCourseId = isset($pathParts[3]) ? $pathParts[3] : null; // 3番目の部分がmaterialCourseId
}
if ($user_id && $materialCourseId) {
    $event_application_register_controller = new EventRegisterController();
    $res = $event_application_register_controller->updateParticipation($user_id, $materialCourseId);
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <script>
        const params = new URLSearchParams(window.location.search);
        const pdfUrl = params.get('file');
        if (pdfUrl) {
            // URLを / で分割して、最後の部分をファイル名として取得
            const parts = pdfUrl.split('/');
            const fileName = parts[parts.length - 1];
            document.title = fileName;
        } else {
            document.title = "PDF Viewer";
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <style>
        canvas {
            display: block;
            margin: auto;
            user-select: none;
            /* テキスト選択禁止 */
            pointer-events: none;
            /* 右クリックメニュー防止 */
        }

        body {
            text-align: center;
        }
    </style>
</head>

<body>
    <style>
        body {
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        #pdf-container {
            margin: 20px auto;
            max-width: 80%;
            display: block;
        }

        canvas {
            display: block;
            margin-bottom: 20px;
            border: 1px solid #ccc;
        }

        .controls {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10;
            background-color: white;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <script>
        let pdfDoc = null;
        let currentPage = 1;
        let scale = 1.5;
        let pageCache = [];

        if (!pdfUrl) {
            document.body.innerHTML = "<p>PDFファイルが指定されていません。</p>";
        } else {
            // PDFを読み込む
            pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
                pdfDoc = pdf;
                for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                    renderPage(pageNum);
                }
            }).catch(err => {
                console.error(err);
                document.getElementById('pdf-container').innerHTML = "<p>PDFの読み込みに失敗しました。</p>";
            });
        }

        function renderPage(pageNum) {
            if (pageCache[pageNum]) {
                return;
            }
            pdfDoc.getPage(pageNum).then(page => {
                const viewport = page.getViewport({
                    scale
                });
                const canvas = document.createElement('canvas');
                const container = document.getElementById('pdf-container');
                container.appendChild(canvas);
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                page.render({
                    canvasContext: context,
                    viewport
                }).promise.then(() => {
                    const fontSize = Math.max(12, Math.round(viewport.height * 0.03));
                    context.font = `${fontSize}px Arial`;
                    context.fillStyle = "black";
                    context.textAlign = "center";;

                    context.strokeStyle = "#000";
                    context.lineWidth = 2;
                    context.strokeRect(0, 0, canvas.width, canvas.height);
                });
                document.getElementById('pdf-container').appendChild(canvas);
                pageCache[pageNum] = true;
            });
        }

        window.addEventListener('scroll', () => {
            const scrollPosition = window.scrollY + window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const pageHeight = document.querySelector('canvas')?.height || 0;
            const newPage = Math.ceil(scrollPosition / pageHeight);

            if (newPage !== currentPage && newPage <= pdfDoc.numPages) {
                currentPage = newPage;
                renderPage(currentPage);
            }
        });

        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('keydown', event => {
            if (event.ctrlKey && (event.key === 's' || event.key === 'S')) {
                event.preventDefault();
            }
        });
    </script>

    <div id="pdf-container"></div>
</body>

</html>