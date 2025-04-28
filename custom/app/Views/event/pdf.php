<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/app/Controllers/event/event_application_register_controller.php');

$user_id = $_SESSION['user_id'] ?? $USER->id;

if (empty($user_id)) {
    redirect(new moodle_url('/custom/app/Views/login/index.php'));
    exit;
}

$file = isset($_GET['file']) ? $_GET['file'] : null;
if ($file) {
    $pathParts = explode('/', $file);
    $materialCourseId = isset($pathParts[3]) ? $pathParts[3] : null;
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
            const parts = pdfUrl.split('/');
            const fileName = parts[parts.length - 1];
            document.title = fileName;
        } else {
            document.title = "PDF Viewer";
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc =
            "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js";
    </script>
    <style>
        canvas {
            display: block;
            margin: auto;
            user-select: none;
            pointer-events: none;
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

    <div id="pdf-container"></div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const pdfUrl = new URLSearchParams(location.search).get("file");
            if (!pdfUrl) {
                document.getElementById("pdf-container").textContent = "PDFファイルが指定されていません。";
                return;
            }
            document.title = pdfUrl.split("/").pop();

            const CMAP_URL = "https://cdn.jsdelivr.net/npm/pdfjs-dist@2.16.105/cmaps/";
            const FONT_URL = "https://cdn.jsdelivr.net/npm/pdfjs-dist@2.16.105/standard_fonts/";

            const container = document.getElementById("pdf-container");
            const pageCache = new Map();
            const scale = 1.5;
            let pdfDoc = null;
            let lastRendered = 0;

            pdfjsLib.getDocument({
                url: pdfUrl,
                cMapUrl: CMAP_URL,
                cMapPacked: true,
                standardFontDataUrl: FONT_URL,
                useWorkerFetch: true
            }).promise.then(doc => {
                pdfDoc = doc;
                renderPage(1);
            }).catch(err => {
                console.error(err);
                container.textContent = "PDF の読み込みに失敗しました。";
            });

            function renderPage(num) {
                if (num > pdfDoc.numPages || pageCache.has(num)) return;
                pageCache.set(num, true);

                pdfDoc.getPage(num).then(page => {
                    const viewport = page.getViewport({
                        scale
                    });
                    const canvas = document.createElement("canvas");
                    const ctx = canvas.getContext("2d");
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    container.appendChild(canvas);

                    page.render({
                        canvasContext: ctx,
                        viewport
                    }).promise.then(() => {
                        ctx.strokeStyle = "#000";
                        ctx.lineWidth = 2;
                        ctx.strokeRect(0, 0, canvas.width, canvas.height);
                        lastRendered = num;
                        observeLastCanvas();
                    });
                });
            }

            let observer = null;

            function observeLastCanvas() {
                if (observer) observer.disconnect();
                const target = container.lastElementChild;
                if (!target) return;

                observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            renderPage(lastRendered + 1);
                        }
                    });
                }, {
                    rootMargin: "200px 0px"
                });
                observer.observe(target);
            }
        });
    </script>

</body>

</html>