<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Google Docs Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Google Sans', 'Arial', sans-serif;
            background: #f9fbff;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 300px;
            background: white;
            border-right: 1px solid #e8eaed;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e8eaed;
            background: #f8f9fa;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: 500;
            color: #3c4043;
            margin-bottom: 12px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dadce0;
            border-radius: 8px;
            font-size: 14px;
            outline: none;
        }

        .questions-container {
            padding: 8px 0;
        }

        .question-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.2s;
            border-left: 3px solid transparent;
        }

        .question-item:hover {
            background-color: #f1f3f4;
        }

        .question-item.active {
            background-color: #e8f0fe;
            border-left-color: #1a73e8;
        }

        .question-chapter {
            font-size: 11px;
            color: #5f6368;
            text-transform: uppercase;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .question-title {
            font-size: 14px;
            color: #3c4043;
            line-height: 1.4;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }

        .toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #e8eaed;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 48px;
            flex-wrap: wrap;
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 0 8px;
            border-right: 1px solid #e8eaed;
        }

        .toolbar-group:last-child {
            border-right: none;
        }

        .toolbar button {
            padding: 8px 12px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .toolbar button:hover {
            background-color: #f1f3f4;
        }

        .toolbar button.active {
            background-color: #e8f0fe;
            color: #1a73e8;
        }

        .status-bar {
            background: #f8f9fa;
            border-bottom: 1px solid #e8eaed;
            padding: 6px 16px;
            font-size: 12px;
            color: #5f6368;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-saved { background: #34a853; }
        .status-saving { background: #fbbc04; }
        .status-error { background: #ea4335; }

        .document-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f9fbff;
        }

        .document-wrapper {
            max-width: 816px;
            margin: 0 auto;
        }

        /* REAL GOOGLE DOCS PAGE DIMENSIONS */
        .document-page {
            width: 816px;
            min-height: 1056px;
            background: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            position: relative;
            border-radius: 0;
            overflow: visible;
            page-break-after: always;
        }

        .page-header {
            padding: 32px 32px 16px 32px;
            border-bottom: 1px solid #e8eaed;
            background: white;
        }

        .question-title-input {
            width: 100%;
            font-size: 20px;
            font-weight: 500;
            border: none;
            outline: none;
            background: transparent;
            color: #3c4043;
            padding: 8px 0;
            font-family: 'Google Sans', sans-serif;
        }

        .question-title-input::placeholder {
            color: #9aa0a6;
        }

        /* REAL GOOGLE DOCS CONTENT AREA */
        .page-content {
            padding: 32px;
            min-height: 952px;
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.15;
            color: #000000;
            outline: none;
            word-wrap: break-word;
            overflow: visible;
            position: relative;
        }

        .page-content:empty::before {
            content: 'Start writing your answer here...';
            color: #9aa0a6;
            font-style: italic;
            pointer-events: none;
        }

        .page-content p {
            margin: 0 0 11pt 0;
            text-align: left;
            min-height: 1.15em; /* Prevent paragraph collapse */
        }

        .page-content p:empty {
            min-height: 1.15em; /* Maintain height for empty paragraphs */
        }

        .page-content p br:only-child {
            display: block; /* Ensure <br> tags are visible */
        }

        .page-content h1, .page-content h2, .page-content h3, 
        .page-content h4, .page-content h5, .page-content h6 {
            font-family: 'Google Sans', sans-serif;
            font-weight: 500;
            margin: 16pt 0 8pt 0;
            color: #202124;
        }

        .page-content h1 { font-size: 20pt; }
        .page-content h2 { font-size: 16pt; }
        .page-content h3 { font-size: 14pt; }

        .page-content ul, .page-content ol {
            margin: 0 0 11pt 0;
            padding-left: 36pt;
        }

        .page-content li {
            margin-bottom: 0;
        }

        .page-number {
            position: absolute;
            bottom: 16px;
            right: 32px;
            font-size: 10px;
            color: #5f6368;
            font-family: 'Google Sans', sans-serif;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #5f6368;
            font-style: italic;
        }

        /* PERFECT PRINT STYLES */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                font-size: 11pt !important;
                line-height: 1.15 !important;
            }
            
            .sidebar, .toolbar, .status-bar {
                display: none !important;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            
            .document-container {
                padding: 0 !important;
                background: white !important;
                overflow: visible !important;
            }
            
            .document-wrapper {
                max-width: none !important;
                margin: 0 !important;
            }
            
            .document-page {
                width: 210mm !important;
                height: 297mm !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                background: white !important;
                position: relative !important;
                overflow: hidden !important;
            }
            
            .document-page:last-child {
                page-break-after: auto !important;
            }
            
            .page-header {
                padding: 25mm 25mm 10mm 25mm !important;
                border-bottom: 1pt solid #333 !important;
                background: white !important;
                margin: 0 !important;
            }
            
            .question-title-input {
                font-size: 16pt !important;
                font-weight: bold !important;
                color: #000 !important;
                font-family: 'Arial', sans-serif !important;
                border: none !important;
                background: transparent !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            .page-content {
                padding: 15mm 25mm 25mm 25mm !important;
                margin: 0 !important;
                min-height: auto !important;
                max-height: none !important;
                overflow: visible !important;
                background: white !important;
                color: #000 !important;
                font-family: 'Times New Roman', serif !important;
                font-size: 11pt !important;
                line-height: 1.15 !important;
            }
            
            .page-content::before {
                display: none !important;
            }
            
            .page-content p {
                margin: 0 0 11pt 0 !important;
                line-height: 1.15 !important;
                text-align: justify !important;
                color: #000 !important;
                orphans: 2 !important;
                widows: 2 !important;
            }
            
            .page-content p:empty,
            .page-content p br:only-child {
                display: none !important;
            }
            
            .page-content h1, .page-content h2, .page-content h3,
            .page-content h4, .page-content h5, .page-content h6 {
                font-family: 'Arial', sans-serif !important;
                font-weight: bold !important;
                margin: 16pt 0 8pt 0 !important;
                color: #000 !important;
                page-break-after: avoid !important;
                orphans: 3 !important;
                widows: 3 !important;
            }
            
            .page-number {
                position: absolute !important;
                bottom: 15mm !important;
                right: 25mm !important;
                font-size: 9pt !important;
                color: #000 !important;
                font-family: 'Arial', sans-serif !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-title">📚 Questions</div>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search questions...">
            </div>
        </div>
        <div id="questionsContainer" class="questions-container">
            <div class="loading">Loading questions...</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-group">
                <button onclick="saveAllChanges()" title="Save All (Ctrl+S)">💾 Save</button>
                <button onclick="exportToPDF()" title="Export to PDF">📄 PDF</button>
            </div>
            
            <div class="toolbar-group">
                <button onclick="toggleFormat('bold')" id="boldBtn" title="Bold (Ctrl+B)"><b>B</b></button>
                <button onclick="toggleFormat('italic')" id="italicBtn" title="Italic (Ctrl+I)"><i>I</i></button>
                <button onclick="toggleFormat('underline')" id="underlineBtn" title="Underline (Ctrl+U)"><u>U</u></button>
            </div>

            <div class="toolbar-group">
                <button onclick="insertList('ul')" title="Bullet List">• List</button>
                <button onclick="insertList('ol')" title="Numbered List">1. List</button>
            </div>

            <div class="toolbar-group">
                <button onclick="applyStyle('h1')" title="Heading 1">H1</button>
                <button onclick="applyStyle('h2')" title="Heading 2">H2</button>
                <button onclick="applyStyle('h3')" title="Heading 3">H3</button>
                <button onclick="applyStyle('p')" title="Normal Text">¶</button>
            </div>

            <div class="toolbar-group">
                <button onclick="insertManualPageBreak()" title="Insert Page Break (Ctrl+Enter)">📄 Break</button>
                <button onclick="testGoogleDocsStyle()" title="Test Google Docs Style">🧪 Test Google Docs</button>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <span class="status-indicator status-saved" id="statusIndicator"></span>
            <span id="statusText">Ready to edit</span>
            <span style="margin-left: auto;" id="pageCount">Page 1 of 1</span>
        </div>

        <!-- Document Container -->
        <div class="document-container">
            <div id="documentWrapper" class="document-wrapper">
                <div class="loading">Loading document...</div>
            </div>
        </div>
    </div>

    <script>
        // REAL GOOGLE DOCS IMPLEMENTATION - EXACT BEHAVIOR
        
        // Sample data
        let data = {
            chapters: [
                {
                    title: "Chapter 1: Introduction",
                    questions: [
                        {
                            title: "What is JavaScript and how does it work in web development?",
                            answers: [{
                                answer: JSON.stringify({
                                    blocks: [
                                        { type: "paragraph", data: { text: "JavaScript is a high-level, interpreted programming language that is widely used for web development. It was originally created to make web pages interactive, but has since evolved to be used in many other contexts including server-side development, mobile app development, and desktop applications." }},
                                        { type: "paragraph", data: { text: "The language was developed by Brendan Eich at Netscape in 1995 and was initially called LiveScript before being renamed to JavaScript. Despite its name, JavaScript has no direct relation to Java programming language." }},
                                        { type: "paragraph", data: { text: "JavaScript is an interpreted language, which means it doesn't need to be compiled before running. The browser's JavaScript engine reads and executes the code line by line. Modern JavaScript engines like V8 (used in Chrome and Node.js) use just-in-time compilation to optimize performance." }},
                                        { type: "paragraph", data: { text: "One of the key features of JavaScript is its dynamic typing system. Variables don't need to be declared with a specific type, and their type can change during runtime. This flexibility makes JavaScript easy to learn but can also lead to unexpected behavior if not handled carefully." }},
                                        { type: "paragraph", data: { text: "In modern web development, JavaScript is essential for creating interactive user interfaces. It can manipulate the Document Object Model (DOM) to dynamically change content, respond to user events, and communicate with servers through AJAX requests." }},
                                        { type: "paragraph", data: { text: "JavaScript supports multiple programming paradigms including procedural, object-oriented, and functional programming. This versatility allows developers to choose the style that best fits their project requirements." }},
                                        { type: "paragraph", data: { text: "The ECMAScript specification defines the standard for JavaScript, with regular updates introducing new features and improvements. ES6 (ES2015) was a major update that introduced classes, arrow functions, template literals, and many other modern features." }},
                                        { type: "paragraph", data: { text: "JavaScript's event-driven nature makes it perfect for handling user interactions like clicks, form submissions, and keyboard input. The event loop mechanism allows JavaScript to handle asynchronous operations efficiently despite being single-threaded." }},
                                        { type: "paragraph", data: { text: "With the introduction of Node.js, JavaScript can now be used for server-side development as well, making it possible to use the same language for both frontend and backend development. This has led to the rise of full-stack JavaScript development." }},
                                        { type: "paragraph", data: { text: "JavaScript frameworks and libraries like React, Angular, and Vue.js have revolutionized web development by providing powerful tools for building complex, interactive applications. These tools abstract away much of the complexity of vanilla JavaScript while providing structure and best practices." }},
                                        { type: "paragraph", data: { text: "The language continues to evolve with new features being added regularly. Modern JavaScript includes features like async/await for handling asynchronous operations, modules for organizing code, and many built-in methods for working with arrays, objects, and other data types." }},
                                        { type: "paragraph", data: { text: "JavaScript's popularity has made it one of the most important programming languages to learn for anyone interested in web development. Its versatility, ease of use, and widespread adoption make it an excellent choice for both beginners and experienced developers." }}
                                    ]
                                })
                            }]
                        },
                        {
                            title: "Explain Variables and Data Types in JavaScript",
                            answers: [{
                                answer: JSON.stringify({
                                    blocks: [
                                        { type: "paragraph", data: { text: "Variables in JavaScript are containers that store data values. They act as named references to memory locations where data is stored, allowing us to access and manipulate that data throughout our program." }}
                                    ]
                                })
                            }]
                        }
                    ]
                }
            ]
        };

        // Global Variables
        let currentChapter = -1;
        let currentQuestion = -1;
        let autoSaveTimer;
        let isLoading = false;
        let currentPageCount = 1;
        let overflowCheckTimer;

        // REAL GOOGLE DOCS CONFIGURATION
        const GOOGLE_DOCS_PAGE_HEIGHT = 952; // Actual content height
        const OVERFLOW_THRESHOLD = 920; // When content starts to overflow
        const CONTENT_PADDING = 64; // Top + bottom padding
        const LINE_HEIGHT_APPROX = 16; // Approximate line height

        // INITIALIZATION
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Real Google Docs Starting...');
            setTimeout(initializeApp, 100);
        });

        function initializeApp() {
            try {
                loadSidebar();
                setupEventListeners();
                updateStatus('Ready to edit', 'status-saved');

                if (data.chapters.length > 0 && data.chapters[0].questions?.length > 0) {
                    setTimeout(() => loadQuestion(0, 0), 300);
                }

                console.log('✅ Real Google Docs Initialized');
            } catch (error) {
                console.error('❌ Initialization error:', error);
                updateStatus('Initialization failed', 'status-error');
            }
        }

        // SIDEBAR MANAGEMENT
        function loadSidebar() {
            const container = document.getElementById('questionsContainer');
            if (!container) return;

            container.innerHTML = '';

            if (!data.chapters?.length) {
                container.innerHTML = '<div class="loading">📝 No chapters available</div>';
                return;
            }

            let totalQuestions = 0;
            data.chapters.forEach((chapter, chapterIndex) => {
                if (chapter.questions?.length) {
                    chapter.questions.forEach((question, questionIndex) => {
                        const questionItem = document.createElement('div');
                        questionItem.className = 'question-item';
                        questionItem.innerHTML = `
                            <div class="question-chapter">${chapter.title || `Chapter ${chapterIndex + 1}`}</div>
                            <div class="question-title">${question.title || `Question ${questionIndex + 1}`}</div>
                        `;

                        questionItem.onclick = () => {
                            loadQuestion(chapterIndex, questionIndex);
                            document.querySelectorAll('.question-item').forEach(item => 
                                item.classList.remove('active'));
                            questionItem.classList.add('active');
                        };

                        container.appendChild(questionItem);
                        totalQuestions++;
                    });
                }
            });

            console.log(`✅ Loaded ${totalQuestions} questions`);
        }

        // QUESTION LOADING
        function loadQuestion(chapterIndex, questionIndex) {
            if (isLoading) return;

            try {
                if (currentChapter >= 0 && currentQuestion >= 0) {
                    saveCurrentContent();
                }

                isLoading = true;
                updateStatus('Loading...', 'status-saving');

                currentChapter = chapterIndex;
                currentQuestion = questionIndex;

                const question = data.chapters[chapterIndex].questions[questionIndex];
                let content = '<p><br></p>';

                if (question.answers?.[0]?.answer) {
                    try {
                        const answerData = JSON.parse(question.answers[0].answer);
                        if (answerData.blocks?.length) {
                            content = convertBlocksToHTML(answerData.blocks);
                        }
                    } catch (error) {
                        console.error('Error parsing answer:', error);
                    }
                }

                // Create initial single page
                const wrapper = document.getElementById('documentWrapper');
                wrapper.innerHTML = `
                    <div class="document-page" id="page-1">
                        <div class="page-header">
                            <input type="text" class="question-title-input" id="questionTitle" 
                                   placeholder="Enter question title..." value="${question.title || ''}">
                        </div>
                        <div class="page-content" id="pageContent-1" contenteditable="true">
                            ${content}
                        </div>
                        <div class="page-number">Page 1</div>
                    </div>
                `;

                currentPageCount = 1;
                updatePageCount();

                setupPageEventListeners(1);

                // Check if content needs to be distributed across multiple pages
                setTimeout(() => {
                    distributeContentSmartly();
                }, 200);

                isLoading = false;
                updateStatus('Document loaded', 'status-saved');

                setTimeout(() => {
                    const pageContent = document.getElementById('pageContent-1');
                    if (pageContent) {
                        pageContent.focus();
                        placeCursorAtEnd(pageContent);
                    }
                }, 100);

            } catch (error) {
                console.error('❌ Question loading error:', error);
                updateStatus('Loading failed', 'status-error');
                isLoading = false;
            }
        }

        // SMART CONTENT DISTRIBUTION (GOOGLE DOCS STYLE)
        function distributeContentSmartly() {
            console.log('📄 Smart content distribution (Google Docs style)...');
            
            const firstPage = document.getElementById('pageContent-1');
            if (!firstPage) return;

            // Check if first page overflows
            if (firstPage.scrollHeight > GOOGLE_DOCS_PAGE_HEIGHT) {
                console.log('📄 Content overflow detected, distributing smartly...');
                
                // Get all content elements
                const allElements = Array.from(firstPage.children);
                let currentHeight = 0;
                let pageNumber = 1;
                let elementsForCurrentPage = [];

                // Process each element and distribute across pages
                allElements.forEach((element, index) => {
                    const elementHeight = element.offsetHeight + 16; // Include margins

                    // Check if adding this element would overflow current page
                    if (currentHeight + elementHeight > OVERFLOW_THRESHOLD && elementsForCurrentPage.length > 0) {
                        // Start new page
                        pageNumber++;
                        
                        // Create new page if it doesn't exist
                        if (!document.getElementById(`page-${pageNumber}`)) {
                            createNewPage(pageNumber);
                        }
                        
                        currentHeight = 0;
                        elementsForCurrentPage = [];
                    }

                    // Add element to current page tracking
                    elementsForCurrentPage.push(element);
                    currentHeight += elementHeight;

                    // Move element to appropriate page if not on page 1
                    if (pageNumber > 1) {
                        const targetPage = document.getElementById(`pageContent-${pageNumber}`);
                        if (targetPage) {
                            // GOOGLE DOCS STYLE: New content goes to TOP, existing shifts down
                            const existingContent = Array.from(targetPage.children);
                            
                            // Remove element from first page
                            element.remove();
                            
                            // Clear target page
                            targetPage.innerHTML = '';
                            
                            // Add new element first (AT TOP)
                            targetPage.appendChild(element);
                            
                            // Then add existing content (shifted down)
                            existingContent.forEach(existingEl => {
                                targetPage.appendChild(existingEl);
                            });
                        }
                    }
                });

                console.log(`✅ Content smartly distributed across ${currentPageCount} pages`);
            }
        }

        function createNewPage(pageNumber) {
            const wrapper = document.getElementById('documentWrapper');
            const newPage = document.createElement('div');
            newPage.className = 'document-page';
            newPage.id = `page-${pageNumber}`;

            newPage.innerHTML = `
                <div class="page-content" id="pageContent-${pageNumber}" contenteditable="true">
                </div>
                <div class="page-number">Page ${pageNumber}</div>
            `;

            wrapper.appendChild(newPage);
            currentPageCount = pageNumber;
            updatePageCount();

            setupPageEventListeners(pageNumber);
            console.log(`✅ Created page ${pageNumber}`);
        }

        // SMART OVERFLOW HANDLING (GOOGLE DOCS STYLE)
        function handleContentOverflowSmart() {
            console.log('🔍 Smart overflow checking (Google Docs style)...');

            for (let pageNum = 1; pageNum <= currentPageCount; pageNum++) {
                const pageContent = document.getElementById(`pageContent-${pageNum}`);
                if (!pageContent) continue;

                const contentHeight = pageContent.scrollHeight;
                
                if (contentHeight > GOOGLE_DOCS_PAGE_HEIGHT) {
                    console.log(`📄 Page ${pageNum} overflow: ${contentHeight}px > ${GOOGLE_DOCS_PAGE_HEIGHT}px`);

                    const nextPageNum = pageNum + 1;
                    
                    // Create next page if it doesn't exist
                    if (!document.getElementById(`page-${nextPageNum}`)) {
                        createNewPage(nextPageNum);
                    }

                    // Move overflowing content with Google Docs style
                    moveOverflowingContentSmart(pageNum, nextPageNum);
                    break;
                }
            }
        }

        function moveOverflowingContentWithPreservation(fromPageNum, toPageNum) {
            const fromPage = document.getElementById(`pageContent-${fromPageNum}`);
            const toPage = document.getElementById(`pageContent-${toPageNum}`);

            if (!fromPage || !toPage) return;

            console.log(`📋 Moving content with preservation: ${fromPageNum} → ${toPageNum}`);

            const fromElements = Array.from(fromPage.children);
            let totalHeight = 0;
            let moveFromIndex = fromElements.length;

            // Find split point
            for (let i = 0; i < fromElements.length; i++) {
                const elementHeight = fromElements[i].offsetHeight + 16;
                
                if (totalHeight + elementHeight > OVERFLOW_THRESHOLD) {
                    moveFromIndex = Math.max(1, i);
                    break;
                }
                totalHeight += elementHeight;
            }

            // Move elements to next page
            if (moveFromIndex < fromElements.length) {
                const elementsToMove = fromElements.slice(moveFromIndex);
                
                console.log(`📄 Moving ${elementsToMove.length} elements to top of page ${toPageNum}`);
                
                // Remove elements to move from fromPage
                elementsToMove.forEach(el => fromPage.removeChild(el));
                
                // Save old content of toPage
                const oldContent = Array.from(toPage.children);
                
                // Clear toPage content
                toPage.innerHTML = '';
                
                // Insert new moved elements first (in original order) - AT TOP
                elementsToMove.forEach(el => {
                    toPage.appendChild(el);
                });
                
                // Then append old content (shifted down)
                oldContent.forEach(el => {
                    toPage.appendChild(el);
                });

                console.log(`✅ Inserted moved elements at top and shifted old content down on page ${toPageNum}`);

                // Check recursively for overflow on toPage after slight delay
                setTimeout(() => {
                    if (toPage.scrollHeight > GOOGLE_DOCS_PAGE_HEIGHT) {
                        handleContentOverflowSmart();
                    }
                }, 100);
            }
        }

        // REAL GOOGLE DOCS OVERFLOW HANDLING
        function handleContentOverflow() {
            console.log('🔍 Checking for content overflow...');

            for (let pageNum = 1; pageNum <= currentPageCount; pageNum++) {
                const pageContent = document.getElementById(`pageContent-${pageNum}`);
                if (!pageContent) continue;

                const contentHeight = pageContent.scrollHeight;
                
                if (contentHeight > GOOGLE_DOCS_PAGE_HEIGHT) {
                    console.log(`📄 Page ${pageNum} overflow: ${contentHeight}px > ${GOOGLE_DOCS_PAGE_HEIGHT}px`);

                    const nextPageNum = pageNum + 1;
                    
                    // Create next page if it doesn't exist
                    if (!document.getElementById(`page-${nextPageNum}`)) {
                        createNewPage(nextPageNum);
                    }

                    // Move overflowing content
                    moveOverflowingContent(pageNum, nextPageNum);
                    break;
                }
            }
        }

        function moveOverflowingContent(fromPageNum, toPageNum) {
            const fromPage = document.getElementById(`pageContent-${fromPageNum}`);
            const toPage = document.getElementById(`pageContent-${toPageNum}`);

            if (!fromPage || !toPage) return;

            console.log(`📋 Moving overflowing content: ${fromPageNum} → ${toPageNum}`);

            const elements = Array.from(fromPage.children);
            let totalHeight = 0;
            let moveFromIndex = elements.length;

            // Find the split point
            for (let i = 0; i < elements.length; i++) {
                const elementHeight = elements[i].offsetHeight + 16;
                
                if (totalHeight + elementHeight > OVERFLOW_THRESHOLD) {
                    moveFromIndex = Math.max(1, i); // Always leave at least one element
                    break;
                }
                totalHeight += elementHeight;
            }

            // Move elements to next page
            if (moveFromIndex < elements.length) {
                const elementsToMove = elements.slice(moveFromIndex);
                
                console.log(`📄 Moving ${elementsToMove.length} elements to top of page ${toPageNum}`);
                
                // Remove elements to move from fromPage
                elementsToMove.forEach(el => fromPage.removeChild(el));
                
                // Save old content of toPage
                const oldContent = Array.from(toPage.children);
                
                // Clear toPage content
                toPage.innerHTML = '';
                
                // Insert new moved elements first (in original order) - AT TOP
                elementsToMove.forEach(el => {
                    toPage.appendChild(el);
                });
                
                // Then append old content (shifted down)
                oldContent.forEach(el => {
                    toPage.appendChild(el);
                });

                console.log(`✅ Inserted moved elements at top and shifted old content down on page ${toPageNum}`);

                // Check recursively for overflow on toPage after slight delay
                setTimeout(() => {
                    if (toPage.scrollHeight > GOOGLE_DOCS_PAGE_HEIGHT) {
                        handleContentOverflowSmart();
                    }
                }, 100);
            }
        }

        // REAL GOOGLE DOCS EVENT LISTENERS
        function setupPageEventListeners(pageNumber) {
            const pageContent = document.getElementById(`pageContent-${pageNumber}`);
            if (!pageContent) return;

            console.log(`🎧 Setting up listeners for page ${pageNumber}`);

            // Real Google Docs input behavior
            pageContent.addEventListener('input', (e) => {
                updateStatus('Editing...', 'status-saving');
                
                clearTimeout(autoSaveTimer);
                clearTimeout(overflowCheckTimer);

                // Check for overflow after short delay (like Google Docs)
                overflowCheckTimer = setTimeout(() => {
                    handleContentOverflowSmart();
                }, 500); // Longer delay for natural typing

                // Auto-save
                autoSaveTimer = setTimeout(() => {
                    saveCurrentContent();
                }, 2000);
                
                // Also check for empty pages after content changes
                setTimeout(() => {
                    removeEmptyPages();
                }, 1000);
            });

            // Real Google Docs Enter key behavior
            pageContent.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    // Let the Enter key work normally first
                    setTimeout(() => {
                        // Only check for overflow after a natural delay
                        const contentHeight = pageContent.scrollHeight;
                        
                        // Only create new page if content significantly overflows
                        if (contentHeight > GOOGLE_DOCS_PAGE_HEIGHT + 50) {
                            console.log(`⏎ Content overflow after Enter: ${contentHeight}px`);
                            
                            const nextPageNum = pageNumber + 1;
                            
                            // Create next page if needed
                            if (!document.getElementById(`page-${nextPageNum}`)) {
                                createNewPage(nextPageNum);
                            }

                            // Move only the overflowing content
                            setTimeout(() => {
                                moveOverflowingContentSmart(pageNumber, nextPageNum);
                            }, 100);
                        }
                    }, 100); // Small delay to let DOM update
                }

                // Manual page break
                if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    insertManualPageBreak();
                }

                // ENHANCED BACKSPACE PAGE MERGE
                if (e.key === 'Backspace') {
                    handleBackspacePageMergeEnhanced(pageNumber, e);
                }
            });

            // Paste handling
            pageContent.addEventListener('paste', (e) => {
                setTimeout(() => {
                    handleContentOverflowSmart();
                }, 300);
            });
        }

        // SMART CONTENT MOVEMENT (PREVENTS OVERLAP)
        function moveOverflowingContentSmart(fromPageNum, toPageNum) {
            const fromPage = document.getElementById(`pageContent-${fromPageNum}`);
            const toPage = document.getElementById(`pageContent-${toPageNum}`);

            if (!fromPage || !toPage) return;

            console.log(`📋 Smart content movement: ${fromPageNum} → ${toPageNum}`);

            const fromElements = Array.from(fromPage.children);
            let totalHeight = 0;
            let moveFromIndex = fromElements.length;

            // Calculate exactly what needs to move
            for (let i = 0; i < fromElements.length; i++) {
                const elementHeight = fromElements[i].offsetHeight + 16;
                
                if (totalHeight + elementHeight > OVERFLOW_THRESHOLD) {
                    moveFromIndex = Math.max(1, i);
                    break;
                }
                totalHeight += elementHeight;
            }

            if (moveFromIndex < fromElements.length) {
                const elementsToMove = fromElements.slice(moveFromIndex);
                
                // Get current selection position for cursor management
                const selection = window.getSelection();
                let cursorInMovedContent = false;
                
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const cursorElement = range.commonAncestorContainer.parentElement;
                    cursorInMovedContent = elementsToMove.some(el => 
                        el.contains(cursorElement) || el === cursorElement
                    );
                }

                // Move elements to target page
                const existingContent = Array.from(toPage.children);
                const hasRealContent = existingContent.some(el => 
                    el.textContent.trim() !== '' && !(el.tagName === 'P' && el.innerHTML === '<br>')
                );

                if (!hasRealContent) {
                    toPage.innerHTML = '';
                }

                elementsToMove.forEach(element => {
                    toPage.appendChild(element);
                });

                console.log(`✅ Smart moved ${elementsToMove.length} elements to page ${toPageNum}`);

                // If cursor was in moved content, focus target page
                if (cursorInMovedContent) {
                    setTimeout(() => {
                        toPage.focus();
                        // Position cursor at start of moved content
                        if (elementsToMove[0]) {
                            const range = document.createRange();
                            const selection = window.getSelection();
                            range.setStart(elementsToMove[0], 0);
                            range.collapse(true);
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                    }, 50);
                }

                // Check recursively for further overflow
                setTimeout(() => {
                    if (toPage.scrollHeight > GOOGLE_DOCS_PAGE_HEIGHT) {
                        handleContentOverflowSmart();
                    }
                }, 150);
            }
        }

        // ENHANCED BACKSPACE WITH BETTER DETECTION
        function handleBackspacePageMergeEnhanced(pageNumber, event) {
            if (pageNumber <= 1) return;

            const currentPage = document.getElementById(`pageContent-${pageNumber}`);
            const previousPage = document.getElementById(`pageContent-${pageNumber - 1}`);
            
            if (!currentPage || !previousPage) return;

            const selection = window.getSelection();
            if (selection.rangeCount === 0) return;

            const range = selection.getRangeAt(0);
            
            // Enhanced detection for page start
            const isAtStart = isAtPageStartEnhanced(currentPage, range);

            if (isAtStart) {
                console.log(`⌫ Enhanced backspace merge: page ${pageNumber} → ${pageNumber - 1}`);
                
                event.preventDefault();
                
                // Enhanced page merge
                mergePagesSmart(pageNumber - 1, pageNumber);
            }
        }

        function isAtPageStartEnhanced(pageContent, range) {
            const startContainer = range.startContainer;
            const startOffset = range.startOffset;

            // More comprehensive start detection
            if (startOffset !== 0) return false;

            // Check if we're at the very beginning
            const walker = document.createTreeWalker(
                pageContent,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );

            const firstTextNode = walker.nextNode();
            
            if (!firstTextNode) {
                // No text content, we're at start
                return true;
            }

            // Check if cursor is at the first text position
            return startContainer === firstTextNode || 
                   (startContainer.nodeType === Node.ELEMENT_NODE && 
                    startContainer === pageContent.firstElementChild);
        }

        function mergePagesSmart(targetPageNum, sourcePageNum) {
            const targetPage = document.getElementById(`pageContent-${targetPageNum}`);
            const sourcePage = document.getElementById(`pageContent-${sourcePageNum}`);
            const sourcePageDiv = document.getElementById(`page-${sourcePageNum}`);

            if (!targetPage || !sourcePage || !sourcePageDiv) return;

            console.log(`🔗 Smart merge: page ${sourcePageNum} → ${targetPageNum}`);

            // Store end position of target page
            const targetEndPosition = getEndPosition(targetPage);
            const sourceElements = Array.from(sourcePage.children);

            // Calculate if content will fit
            const targetHeight = targetPage.scrollHeight;
            const sourceHeight = sourcePage.scrollHeight;
            const totalHeight = targetHeight + sourceHeight;

            if (totalHeight <= GOOGLE_DOCS_PAGE_HEIGHT + 100) {
                // Direct merge possible
                console.log(`✅ Direct merge possible`);
                
                sourceElements.forEach(element => {
                    if (element.textContent.trim() !== '' || 
                        !(element.tagName === 'P' && element.innerHTML === '<br>')) {
                        targetPage.appendChild(element.cloneNode(true));
                    }
                });

                // Remove source page
                sourcePageDiv.remove();
                currentPageCount--;
                updatePageCount();
                renumberPages();

                // Position cursor at merge point
                setTimeout(() => {
                    positionCursorAtMergePoint(targetPage, targetEndPosition);
                }, 50);

            } else {
                // Partial merge
                console.log(`⚠️ Partial merge required`);
                
                let targetCurrentHeight = targetHeight;
                let elementsMoved = 0;

                for (let i = 0; i < sourceElements.length; i++) {
                    const element = sourceElements[i];
                    const elementHeight = element.offsetHeight + 16;

                    if (targetCurrentHeight + elementHeight <= OVERFLOW_THRESHOLD) {
                        if (element.textContent.trim() !== '' || 
                            !(element.tagName === 'P' && element.innerHTML === '<br>')) {
                            targetPage.appendChild(element.cloneNode(true));
                            element.remove();
                            elementsMoved++;
                            targetCurrentHeight += elementHeight;
                        }
                    } else {
                        break;
                    }
                }

                console.log(`📋 Partial merge: moved ${elementsMoved} elements`);

                // Position cursor
                setTimeout(() => {
                    positionCursorAtMergePoint(targetPage, targetEndPosition);
                }, 50);

                // Check if source page is now empty
                const remainingElements = Array.from(sourcePage.children).filter(el => 
                    el.textContent.trim() !== '' && !(el.tagName === 'P' && el.innerHTML === '<br>')
                );

                if (remainingElements.length === 0) {
                    sourcePageDiv.remove();
                    currentPageCount--;
                    updatePageCount();
                    renumberPages();
                }
            }

            // Cleanup empty pages
            setTimeout(() => {
                removeEmptyPages();
            }, 100);
        }

        function smartMergePages(targetPageNum, sourcePageNum) {
            const targetPage = document.getElementById(`pageContent-${targetPageNum}`);
            const sourcePage = document.getElementById(`pageContent-${sourcePageNum}`);

            if (!targetPage || !sourcePage) return;

            const targetEndPosition = getEndPosition(targetPage);
            const sourceElements = Array.from(sourcePage.children);
            
            let targetCurrentHeight = targetPage.scrollHeight;
            let elementsMoved = 0;

            // Try to move as many elements as possible from source to target
            for (let i = 0; i < sourceElements.length; i++) {
                const element = sourceElements[i];
                const elementHeight = element.offsetHeight + 16;

                if (targetCurrentHeight + elementHeight <= OVERFLOW_THRESHOLD) {
                    // Element fits - move it
                    if (!(element.tagName === 'P' && element.innerHTML === '<br>')) {
                        targetPage.appendChild(element.cloneNode(true));
                        element.remove();
                        elementsMoved++;
                        targetCurrentHeight += elementHeight;
                    }
                } else {
                    // Element doesn't fit - stop merging
                    break;
                }
            }

            console.log(`📋 Moved ${elementsMoved} elements in smart merge`);

            // Position cursor at merge point
            setTimeout(() => {
                positionCursorAtMergePoint(targetPage, targetEndPosition);
            }, 50);

            // Check if source page is now empty
            const remainingElements = Array.from(sourcePage.children).filter(el => 
                !(el.tagName === 'P' && el.innerHTML === '<br>')
            );

            if (remainingElements.length === 0) {
                // Source page is empty - remove it
                const sourcePageDiv = document.getElementById(`page-${sourcePageNum}`);
                if (sourcePageDiv) {
                    sourcePageDiv.remove();
                    currentPageCount--;
                    updatePageCount();
                    renumberPages();
                }
            }

            // Also cleanup any other empty pages
            setTimeout(() => {
                removeEmptyPages();
            }, 100);
        }

        function getEndPosition(pageContent) {
            const range = document.createRange();
            range.selectNodeContents(pageContent);
            range.collapse(false);
            return {
                container: range.endContainer,
                offset: range.endOffset
            };
        }

        function positionCursorAtMergePoint(targetPage, endPosition) {
            try {
                targetPage.focus();
                
                const range = document.createRange();
                const selection = window.getSelection();
                
                // Try to position at the stored end position
                if (endPosition.container && endPosition.container.parentNode) {
                    range.setStart(endPosition.container, endPosition.offset);
                } else {
                    // Fallback - position at end of page
                    range.selectNodeContents(targetPage);
                    range.collapse(false);
                }
                
                selection.removeAllRanges();
                selection.addRange(range);
                
                console.log(`✅ Cursor positioned at merge point`);
            } catch (error) {
                console.error('Error positioning cursor:', error);
                // Simple fallback
                targetPage.focus();
            }
        }

        function renumberPages() {
            console.log(`🔢 Renumbering pages - total: ${currentPageCount}`);
            
            const wrapper = document.getElementById('documentWrapper');
            const pages = wrapper.querySelectorAll('.document-page');
            
            pages.forEach((page, index) => {
                const newPageNum = index + 1;
                const oldPageNum = parseInt(page.id.split('-')[1]);
                
                if (oldPageNum !== newPageNum) {
                    // Update page ID
                    page.id = `page-${newPageNum}`;
                    
                    // Update page content ID
                    const pageContent = page.querySelector('.page-content');
                    if (pageContent) {
                        pageContent.id = `pageContent-${newPageNum}`;
                    }
                    
                    // Update page number display
                    const pageNumber = page.querySelector('.page-number');
                    if (pageNumber) {
                        pageNumber.textContent = `Page ${newPageNum}`;
                    }
                    
                    // Re-setup event listeners with new page number
                    setupPageEventListeners(newPageNum);
                }
            });
            
            console.log(`✅ Pages renumbered successfully`);
        }

        // REMOVE EMPTY PAGES FUNCTION (FIXED)
        function removeEmptyPages() {
            console.log('🗂️ Checking for empty pages to remove...');
            
            for (let pageNum = 2; pageNum <= currentPageCount; pageNum++) {
                const pageContent = document.getElementById(`pageContent-${pageNum}`);
                const pageDiv = document.getElementById(`page-${pageNum}`);
                
                if (pageContent && pageDiv) {
                    // Check if page is essentially empty
                    const textContent = pageContent.textContent.trim();
                    const hasOnlyBr = pageContent.innerHTML.trim() === '<p><br></p>' || 
                                     pageContent.innerHTML.trim() === '<br>' ||
                                     pageContent.innerHTML.trim() === '';
                    
                    if (textContent === '' || hasOnlyBr) {
                        console.log(`🗑️ Removing empty page ${pageNum}`);
                        pageDiv.remove();
                        currentPageCount--;
                        updatePageCount();
                        renumberPages();
                        break; // Check again after removal
                    }
                }
            }
        }

        // MANUAL PAGE BREAK (Ctrl+Enter)
        function insertManualPageBreak() {
            const selection = window.getSelection();
            if (selection.rangeCount === 0) return;

            const range = selection.getRangeAt(0);
            const pageContent = range.commonAncestorContainer.closest('[id^="pageContent-"]');
            if (!pageContent) return;

            const currentPageNum = parseInt(pageContent.id.split('-')[1]);
            const nextPageNum = currentPageNum + 1;

            console.log(`📄 Manual page break: ${currentPageNum} → ${nextPageNum}`);

            // Create next page
            if (!document.getElementById(`page-${nextPageNum}`)) {
                createNewPage(nextPageNum);
            }

            // Move content after cursor to next page
            const allElements = Array.from(pageContent.children);
            const cursorElement = range.startContainer.parentElement;
            const currentElementIndex = allElements.indexOf(cursorElement);
            
            if (currentElementIndex >= 0) {
                const elementsToMove = allElements.slice(currentElementIndex + 1);
                const toPage = document.getElementById(`pageContent-${nextPageNum}`);

                toPage.innerHTML = '';
                elementsToMove.forEach(element => toPage.appendChild(element));

                if (toPage.children.length === 0) {
                    toPage.innerHTML = '<p><br></p>';
                }

                setTimeout(() => {
                    toPage.focus();
                }, 100);
            }
        }

        // CONTENT CONVERSION
        function convertBlocksToHTML(blocks) {
            if (!blocks?.length) return '<p><br></p>';

            return blocks.map(block => {
                if (!block?.type) return '';

                switch (block.type) {
                    case 'paragraph':
                        const text = block.data?.text || '';
                        return text ? `<p>${text}</p>` : '<p><br></p>';
                        
                    case 'header':
                        const level = block.data?.level || 1;
                        const headerText = block.data?.text || '';
                        return `<h${level}>${headerText}</h${level}>`;
                        
                    case 'list':
                        if (!block.data?.items?.length) return '<ul><li><br></li></ul>';
                        const listType = block.data.style === 'ordered' ? 'ol' : 'ul';
                        const items = block.data.items.map(item => `<li>${item || '<br>'}</li>`).join('');
                        return `<${listType}>${items}</${listType}>`;
                        
                    default:
                        return `<p>${block.data?.text || '<br>'}</p>`;
                }
            }).join('') || '<p><br></p>';
        }

        function convertHTMLToBlocks(html) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            
            const blocks = [];
            let blockId = 1;
            
            Array.from(tempDiv.children).forEach(element => {
                const tagName = element.tagName.toLowerCase();
                
                if (tagName.match(/^h[1-6]$/)) {
                    blocks.push({
                        id: `block_${blockId++}`,
                        type: 'header',
                        data: {
                            text: element.innerHTML,
                            level: parseInt(tagName.substring(1))
                        }
                    });
                } else if (tagName === 'p') {
                    blocks.push({
                        id: `block_${blockId++}`,
                        type: 'paragraph',
                        data: {
                            text: element.innerHTML
                        }
                    });
                } else if (tagName === 'ul' || tagName === 'ol') {
                    const items = Array.from(element.children).map(li => li.innerHTML);
                    blocks.push({
                        id: `block_${blockId++}`,
                        type: 'list',
                        data: {
                            style: tagName === 'ol' ? 'ordered' : 'unordered',
                            items: items
                        }
                    });
                }
            });
            
            return blocks.length ? blocks : [{
                id: 'block_1',
                type: 'paragraph',
                data: { text: '' }
            }];
        }

        // CONTENT MANAGEMENT
        function saveCurrentContent() {
            if (currentChapter < 0 || currentQuestion < 0) return;

            const titleInput = document.getElementById('questionTitle');
            const title = titleInput ? titleInput.value : '';
            
            // Get all content from all pages
            let allContent = '';
            for (let i = 1; i <= currentPageCount; i++) {
                const pageContent = document.getElementById(`pageContent-${i}`);
                if (pageContent) {
                    allContent += pageContent.innerHTML;
                }
            }
            
            // Update question title
            data.chapters[currentChapter].questions[currentQuestion].title = title;
            
            // Convert HTML to blocks
            const blocks = convertHTMLToBlocks(allContent);
            
            if (!data.chapters[currentChapter].questions[currentQuestion].answers) {
                data.chapters[currentChapter].questions[currentQuestion].answers = [{
                    id: 1,
                    user_id: 1,
                    author_first_name: "User",
                    author_last_name: "",
                    answer: JSON.stringify({ time: Date.now(), blocks: blocks })
                }];
            } else {
                data.chapters[currentChapter].questions[currentQuestion].answers[0].answer = JSON.stringify({ 
                    time: Date.now(), 
                    blocks: blocks 
                });
            }

            updateStatus('Auto-saved', 'status-saved');
            loadSidebar(); // Update sidebar

            console.log('💾 Content saved');
        }

        function saveAllChanges() {
            if (currentChapter >= 0 && currentQuestion >= 0) {
                saveCurrentContent();
            }
            updateStatus('All changes saved', 'status-saved');
        }

        // PDF EXPORT
        function exportToPDF() {
            updateStatus('Generating PDF...', 'status-saving');
            
            if (currentChapter >= 0 && currentQuestion >= 0) {
                saveCurrentContent();
            }

            setTimeout(() => {
                window.print();
                updateStatus('PDF generated', 'status-saved');
            }, 500);
        }

        // UTILITY FUNCTIONS
        function placeCursorAtEnd(element) {
            const range = document.createRange();
            const selection = window.getSelection();
            range.selectNodeContents(element);
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        function updateStatus(message, className) {
            const statusText = document.getElementById('statusText');
            const statusIndicator = document.getElementById('statusIndicator');
            
            if (statusText) statusText.textContent = message;
            if (statusIndicator) {
                statusIndicator.className = 'status-indicator ' + className;
            }
        }

        function updatePageCount() {
            const pageCountElement = document.getElementById('pageCount');
            if (pageCountElement) {
                pageCountElement.textContent = `Page 1 of ${currentPageCount}`;
            }
        }

        // FORMATTING FUNCTIONS
        function execCommand(command, value = null) {
            document.execCommand(command, false, value);
            updateToolbarState();
        }

        function toggleFormat(command) {
            execCommand(command);
        }

        function insertList(listType) {
            const command = listType === 'ul' ? 'insertUnorderedList' : 'insertOrderedList';
            execCommand(command);
        }

        function applyStyle(tag) {
            execCommand('formatBlock', tag);
        }

        function updateToolbarState() {
            const commands = ['bold', 'italic', 'underline'];
            commands.forEach(command => {
                const button = document.getElementById(command + 'Btn');
                if (button) {
                    button.classList.toggle('active', document.queryCommandState(command));
                }
            });
        }

        function filterQuestions(searchTerm) {
            const container = document.getElementById('questionsContainer');
            const items = container.querySelectorAll('.question-item');
            
            items.forEach(item => {
                const chapterText = item.querySelector('.question-chapter').textContent.toLowerCase();
                const questionText = item.querySelector('.question-title').textContent.toLowerCase();
                const matches = chapterText.includes(searchTerm.toLowerCase()) || 
                               questionText.includes(searchTerm.toLowerCase());
                
                item.style.display = matches ? 'block' : 'none';
            });
        }

        // EVENT LISTENERS SETUP
        function setupEventListeners() {
            // Title input
            const titleInput = document.getElementById('questionTitle');
            if (titleInput) {
                titleInput.addEventListener('input', () => {
                    updateStatus('Editing title...', 'status-saving');
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(saveCurrentContent, 1000);
                });
            }

            // Search
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    filterQuestions(e.target.value);
                });
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case 'b':
                            e.preventDefault();
                            toggleFormat('bold');
                            break;
                        case 'i':
                            e.preventDefault();
                            toggleFormat('italic');
                            break;
                        case 'u':
                            e.preventDefault();
                            toggleFormat('underline');
                            break;
                        case 's':
                            e.preventDefault();
                            saveAllChanges();
                            break;
                        case 'p':
                            e.preventDefault();
                            exportToPDF();
                            break;
                    }
                }
            });

            document.addEventListener('selectionchange', updateToolbarState);
            console.log('✅ Event listeners setup complete');
        }

        // TEST FUNCTION TO VERIFY GOOGLE DOCS BEHAVIOR
        function testGoogleDocsStyle() {
            console.log('🧪 TESTING GOOGLE DOCS STYLE BEHAVIOR');
            
            // Clear all pages first
            const wrapper = document.getElementById('documentWrapper');
            wrapper.innerHTML = `
                <div class="document-page" id="page-1">
                    <div class="page-header">
                        <input type="text" class="question-title-input" id="questionTitle" 
                               placeholder="Enter question title..." value="Test Question">
                    </div>
                    <div class="page-content" id="pageContent-1" contenteditable="true">
                        <p>Page 1 - First Content</p>
                        <p>Page 1 - Second Content</p>
                        <p>Page 1 - Third Content</p>
                    </div>
                    <div class="page-number">Page 1</div>
                </div>
                <div class="document-page" id="page-2">
                    <div class="page-content" id="pageContent-2" contenteditable="true">
                        <p>Page 2 - Existing Content A</p>
                        <p>Page 2 - Existing Content B</p>
                    </div>
                    <div class="page-number">Page 2</div>
                </div>
            `;
            
            currentPageCount = 2;
            updatePageCount();
            
            // Setup listeners
            setupPageEventListeners(1);
            setupPageEventListeners(2);
            
            // Add lots of content to page 1 to force overflow
            const firstPage = document.getElementById('pageContent-1');
            for (let i = 4; i <= 25; i++) {
                const newP = document.createElement('p');
                newP.textContent = `Page 1 - Content ${i} (This should move to top of page 2)`;
                firstPage.appendChild(newP);
            }
            
            // Trigger overflow handling
            setTimeout(() => {
                console.log('🚀 TRIGGERING GOOGLE DOCS STYLE OVERFLOW...');
                handleContentOverflowSmart();
                
                setTimeout(() => {
                    const page1 = document.getElementById('pageContent-1');
                    const page2 = document.getElementById('pageContent-2');
                    
                    console.log('📊 FINAL RESULT:');
                    console.log(`Page 1 elements: ${page1.children.length}`);
                    console.log(`Page 2 elements: ${page2.children.length}`);
                    
                    console.log('📄 Page 2 content order:');
                    Array.from(page2.children).forEach((el, index) => {
                        console.log(`${index + 1}. ${el.textContent.substring(0, 50)}...`);
                    });
                    
                    updateStatus('Google Docs test completed - check console', 'status-saved');
                }, 1000);
            }, 500);
        }

        // Initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeApp);
        } else {
            setTimeout(initializeApp, 100);
        }
    </script>
</body>
</html>