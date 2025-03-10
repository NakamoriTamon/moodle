<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sample.pdf</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

    <style>
        /* PDF外の領域の背景色をグレーにする */
        body {
            background-color: #f0f0f0;
            /* 背景色をグレーに */
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* PDFの表示領域のスタイル */
        #pdf-container {
            margin: 20px auto;
            max-width: 80%;
            display: block;
            /* flexboxではなく、通常のblock表示 */
        }

        canvas {
            display: block;
            /* canvasをブロック要素として縦に並べる */
            margin-bottom: 20px;
            /* ページ間のスペース */
            border: 1px solid #ccc;
            /* キャンバスの枠線 */
        }

        /* ズームとページ切り替えのボタンをスタイル */
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

        // URLパラメータからPDFのURLを取得
        const params = new URLSearchParams(window.location.search);
        const pdfUrl = params.get('file');

        if (!pdfUrl) {
            document.body.innerHTML = "<p>PDFファイルが指定されていません。</p>";
        } else {
            // PDFを読み込む
            pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
                pdfDoc = pdf;
                renderPage(currentPage); // 最初のページをレンダリング
            });
        }

        // ページをレンダリングする関数
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
                container.appendChild(canvas); // 新しいcanvasを追加
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // ページの描画
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                page.render(renderContext);

                // ページ番号を表示
                context.font = "16px Arial";
                context.fillStyle = "black";
                context.textAlign = "center";
                context.fillText(`Page ${pageNum}`, canvas.width - 50, canvas.height - 20);

                // ページの区切り線を描画
                context.strokeStyle = "#000";
                context.lineWidth = 2;
                context.strokeRect(0, 0, canvas.width, canvas.height);

                // ページをキャッシュ
                pageCache[pageNum] = canvas;
            });
        }

        // スクロールイベントを監視
        window.addEventListener('scroll', () => {
            const scrollPosition = window.scrollY + window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            // 現在表示されているページを計算
            const pageHeight = document.querySelector('canvas')?.height || 0;
            const newPage = Math.ceil(scrollPosition / pageHeight);

            if (newPage !== currentPage && newPage <= pdfDoc.numPages) {
                currentPage = newPage;
                renderPage(currentPage); // 次のページをレンダリング
            }
        });

        // ズームイン
        document.getElementById('zoom-in').addEventListener('click', () => {
            scale += 0.1;
            renderPage(currentPage);
        });

        // ズームアウト
        document.getElementById('zoom-out').addEventListener('click', () => {
            if (scale > 0.2) {
                scale -= 0.1;
                renderPage(currentPage);
            }
        });

        // 右クリックやショートカットキーでの保存を防ぐ
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('keydown', event => {
            if (event.ctrlKey && (event.key === 's' || event.key === 'S')) {
                event.preventDefault();
            }
        });
    </script>

    <!-- PDFの表示領域 -->
    <div id="pdf-container"></div>
</body>

</html>