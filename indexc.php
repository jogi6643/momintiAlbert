<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuClone - Google Docs Alternative</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 20px;
            font-weight: bold;
            color: #1a73e8;
        }

        .document-title {
            font-size: 18px;
            border: none;
            background: transparent;
            outline: none;
            padding: 4px 8px;
            border-radius: 4px;
            min-width: 200px;
        }

        .document-title:hover {
            background: #f1f3f4;
        }

        .toolbar {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
            position: sticky;
            top: 57px;
            z-index: 999;
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 2px;
            padding: 0 8px;
            border-right: 1px solid #e0e0e0;
        }

        .toolbar-group:last-child {
            border-right: none;
        }

        .toolbar-btn {
            background: none;
            border: none;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .toolbar-btn:hover {
            background: #f1f3f4;
        }

        .toolbar-btn.active {
            background: #e3f2fd;
            color: #1976d2;
        }

        .toolbar-select {
            background: none;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .toolbar-select:hover {
            background: #f1f3f4;
        }

        .color-picker {
            position: relative;
            display: inline-block;
        }

        .color-palette {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px;
            display: none;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .color-palette.active {
            display: block;
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 4px;
        }

        .color-option {
            width: 24px;
            height: 24px;
            border: 1px solid #ddd;
            border-radius: 2px;
            cursor: pointer;
        }

        .color-option:hover {
            transform: scale(1.1);
        }

        .main-container {
            display: flex;
            height: calc(100vh - 114px);
        }

        .sidebar {
            width: 250px;
            background: white;
            border-right: 1px solid #e0e0e0;
            padding: 16px;
            overflow-y: auto;
        }

        .sidebar h3 {
            margin-bottom: 16px;
            color: #5f6368;
            font-size: 14px;
            font-weight: 500;
        }

        .document-list {
            list-style: none;
        }

        .document-item {
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .document-item:hover {
            background: #f1f3f4;
        }

        .document-item.active {
            background: #e3f2fd;
            color: #1976d2;
        }

        .editor-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
            overflow: hidden;
        }

        .editor-wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            overflow-y: auto;
            padding: 20px;
        }

        .editor {
            width: 816px;
            min-height: 1056px;
            max-height: 1056px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 96px 72px;
            outline: none;
            font-size: 11pt;
            line-height: 1.6;
            font-family: 'Times New Roman', serif;
            overflow: hidden;
            position: relative;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .document-page {
            width: 816px;
            min-height: 1056px;
            max-height: 1056px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 96px 72px;
            outline: none;
            font-size: 11pt;
            line-height: 1.6;
            font-family: 'Times New Roman', serif;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }

        .page-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 10px;
            color: #666;
            pointer-events: none;
        }

        .status-bar {
            background: white;
            border-top: 1px solid #e0e0e0;
            padding: 8px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #5f6368;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 500;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #1557b0;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        .btn-secondary:hover {
            background: #e8eaed;
        }

        .table-modal table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .table-modal th,
        .table-modal td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table-modal th {
            background: #f8f9fa;
        }

        .comment-panel {
            width: 300px;
            background: white;
            border-left: 1px solid #e0e0e0;
            padding: 16px;
            overflow-y: auto;
        }

        .comment-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .comment-author {
            font-weight: 500;
            font-size: 12px;
            color: #1a73e8;
            margin-bottom: 4px;
        }

        .comment-text {
            font-size: 14px;
            line-height: 1.4;
        }

        .comment-time {
            font-size: 11px;
            color: #666;
            margin-top: 4px;
        }

        .zoom-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .zoom-btn {
            background: none;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .zoom-btn:hover {
            background: #f1f3f4;
        }

        .word-count {
            font-size: 12px;
            color: #5f6368;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            
            .comment-panel {
                display: none;
            }
            
            .editor {
                width: 100%;
                padding: 48px 36px;
            }
            
            .toolbar {
                padding: 4px 8px;
            }
            
            .toolbar-btn {
                min-width: 28px;
                height: 28px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <i class="fas fa-file-alt"></i>
            DocuClone
        </div>
        <input type="text" class="document-title" value="Untitled Document" id="documentTitle">
        <div style="margin-left: auto; display: flex; gap: 8px;">
            <button class="toolbar-btn" onclick="shareDocument()">
                <i class="fas fa-share-alt"></i>
            </button>
            <button class="toolbar-btn" onclick="showComments()">
                <i class="fas fa-comment"></i>
            </button>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-group">
            <button class="toolbar-btn" onclick="newDocument()">
                <i class="fas fa-file-plus"></i>
            </button>
            <button class="toolbar-btn" onclick="openDocument()">
                <i class="fas fa-folder-open"></i>
            </button>
            <button class="toolbar-btn" onclick="saveDocument()">
                <i class="fas fa-save"></i>
            </button>
            <button class="toolbar-btn" onclick="printDocument()">
                <i class="fas fa-print"></i>
            </button>
        </div>

        <div class="toolbar-group">
            <button class="toolbar-btn" onclick="undo()">
                <i class="fas fa-undo"></i>
            </button>
            <button class="toolbar-btn" onclick="redo()">
                <i class="fas fa-redo"></i>
            </button>
        </div>

        <div class="toolbar-group">
            <select class="toolbar-select" id="fontFamily" onchange="changeFontFamily()">
                <option value="Times New Roman">Times New Roman</option>
                <option value="Arial">Arial</option>
                <option value="Helvetica">Helvetica</option>
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
                <option value="Courier New">Courier New</option>
            </select>
            <select class="toolbar-select" id="fontSize" onchange="changeFontSize()">
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11" selected>11</option>
                <option value="12">12</option>
                <option value="14">14</option>
                <option value="16">16</option>
                <option value="18">18</option>
                <option value="20">20</option>
                <option value="24">24</option>
                <option value="32">32</option>
                <option value="48">48</option>
            </select>
        </div>

        <div class="toolbar-group">
            <button class="toolbar-btn" id="boldBtn" onclick="formatText('bold')">
                <i class="fas fa-bold"></i>
            </button>
            <button class="toolbar-btn" id="italicBtn" onclick="formatText('italic')">
                <i class="fas fa-italic"></i>
            </button>
            <button class="toolbar-btn" id="underlineBtn" onclick="formatText('underline')">
                <i class="fas fa-underline"></i>
            </button>
        </div>

        <div class="toolbar-group">
            <div class="color-picker">
                <button class="toolbar-btn" onclick="toggleColorPicker('textColor')">
                    <i class="fas fa-font" style="color: #000;"></i>
                </button>
                <div class="color-palette" id="textColorPalette">
                    <div class="color-grid" id="textColorGrid"></div>
                </div>
            </div>
            <div class="color-picker">
                <button class="toolbar-btn" onclick="toggleColorPicker('highlightColor')">
                    <i class="fas fa-highlighter" style="color: #ffff00;"></i>
                </button>
                <div class="color-palette" id="highlightColorPalette">
                    <div class="color-grid" id="highlightColorGrid"></div>
                </div>
            </div>
        </div>

        <div class="toolbar-group">
            <button class="toolbar-btn" onclick="formatText('justifyLeft')">
                <i class="fas fa-align-left"></i>
            </button>
            <button class="toolbar-btn" onclick="formatText('justifyCenter')">
                <i class="fas fa-align-center"></i>
            </button>
            <button class="toolbar-btn" onclick="formatText('justifyRight')">
                <i class="fas fa-align-right"></i>
            </button>
            <button class="toolbar-btn" onclick="formatText('justifyFull')">
                <i class="fas fa-align-justify"></i>
            </button>
        </div>

        <div class="toolbar-group">
            <button class="toolbar-btn" onclick="formatText('insertOrderedList')">
                <i class="fas fa-list-ol"></i>
            </button>
            <button class="toolbar-btn" onclick="formatText('insertUnorderedList')">
                <i class="fas fa-list-ul"></i>
            </button>
            <button class="toolbar-btn" onclick="formatText('outdent')">
                <i class="fas fa-outdent"></i>
            </button>
            <button class="toolbar-btn" onclick="formatText('indent')">
                <i class="fas fa-indent"></i>
            </button>
        </div>

        <div class="toolbar-group">
            <button class="toolbar-btn" onclick="insertLink()">
                <i class="fas fa-link"></i>
            </button>
            <button class="toolbar-btn" onclick="insertImage()">
                <i class="fas fa-image"></i>
            </button>
            <button class="toolbar-btn" onclick="insertTable()">
                <i class="fas fa-table"></i>
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Recent Documents</h3>
            <ul class="document-list" id="documentList">
                <li class="document-item active">
                    <i class="fas fa-file-alt"></i>
                    <span>Untitled Document</span>
                </li>
            </ul>
            
            <h3 style="margin-top: 24px;">Templates</h3>
            <ul class="document-list">
                <li class="document-item" onclick="loadTemplate('blank')">
                    <i class="fas fa-file"></i>
                    <span>Blank Document</span>
                </li>
                <li class="document-item" onclick="loadTemplate('resume')">
                    <i class="fas fa-user"></i>
                    <span>Resume</span>
                </li>
                <li class="document-item" onclick="loadTemplate('letter')">
                    <i class="fas fa-envelope"></i>
                    <span>Letter</span>
                </li>
            </ul>
        </div>

        <!-- Editor Container -->
        <div class="editor-container">
            <div class="editor-wrapper">
                <div class="page-wrapper" id="pageWrapper">
                    <div class="document-page" id="page-1">
                        <div class="editor" id="editor" contenteditable="true" 
                             oninput="handleInput(event)" 
                             onkeyup="updateFormatButtons()"
                             onmouseup="updateFormatButtons()"
                             onkeydown="handleKeyDown(event)">
                            <p>Start typing your document here...</p>
                        </div>
                        <div class="page-number">Page 1</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comment Panel -->
        <div class="comment-panel" id="commentPanel" style="display: none;">
            <h3>Comments</h3>
            <div id="commentsList">
                <div class="comment-item">
                    <div class="comment-author">John Doe</div>
                    <div class="comment-text">Great work on this document!</div>
                    <div class="comment-time">2 hours ago</div>
                </div>
            </div>
            <div style="margin-top: 16px;">
                <textarea placeholder="Add a comment..." style="width: 100%; height: 60px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                <button class="btn" style="margin-top: 8px; width: 100%;">Add Comment</button>
            </div>
        </div>
    </div>

    <!-- Status Bar -->
    <div class="status-bar">
        <div class="word-count" id="wordCount">0 words</div>
        <div class="zoom-controls">
            <button class="zoom-btn" onclick="zoomOut()">-</button>
            <span id="zoomLevel">100%</span>
            <button class="zoom-btn" onclick="zoomIn()">+</button>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal" id="linkModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Insert Link</h3>
                <button class="close-btn" onclick="closeModal('linkModal')">&times;</button>
            </div>
            <div class="form-group">
                <label class="form-label">Text to display:</label>
                <input type="text" class="form-input" id="linkText" placeholder="Link text">
            </div>
            <div class="form-group">
                <label class="form-label">URL:</label>
                <input type="url" class="form-input" id="linkUrl" placeholder="https://example.com">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeModal('linkModal')">Cancel</button>
                <button class="btn" onclick="insertLinkConfirm()">Insert</button>
            </div>
        </div>
    </div>

    <div class="modal" id="imageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Insert Image</h3>
                <button class="close-btn" onclick="closeModal('imageModal')">&times;</button>
            </div>
            <div class="form-group">
                <label class="form-label">Image URL:</label>
                <input type="url" class="form-input" id="imageUrl" placeholder="https://example.com/image.jpg">
            </div>
            <div class="form-group">
                <label class="form-label">Alt text:</label>
                <input type="text" class="form-input" id="imageAlt" placeholder="Image description">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeModal('imageModal')">Cancel</button>
                <button class="btn" onclick="insertImageConfirm()">Insert</button>
            </div>
        </div>
    </div>

    <div class="modal" id="tableModal">
        <div class="modal-content table-modal">
            <div class="modal-header">
                <h3 class="modal-title">Insert Table</h3>
                <button class="close-btn" onclick="closeModal('tableModal')">&times;</button>
            </div>
            <div class="form-group">
                <label class="form-label">Rows:</label>
                <input type="number" class="form-input" id="tableRows" value="3" min="1" max="20">
            </div>
            <div class="form-group">
                <label class="form-label">Columns:</label>
                <input type="number" class="form-input" id="tableCols" value="3" min="1" max="10">
            </div>
            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                <button class="btn btn-secondary" onclick="closeModal('tableModal')">Cancel</button>
                <button class="btn" onclick="insertTableConfirm()">Insert</button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentDocument = {
            title: 'Untitled Document',
            content: '<p>Start typing your document here...</p>',
            id: Date.now()
        };
        let documents = [currentDocument];
        let zoomLevel = 100;
        let undoStack = [];
        let redoStack = [];
        let currentPageCount = 1;
        let isTyping = false;

        // Page management constants
        const MAX_PAGE_HEIGHT = 864; // Maximum content height per page
        const PAGE_PADDING = 96; // Top and bottom padding

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeColorPickers();
            loadDocuments();
            updateWordCount();
            
            // Auto-save functionality
            setInterval(autoSave, 30000); // Auto-save every 30 seconds
        });

        // Handle input and check for page overflow
        function handleInput(event) {
            updateWordCount();
            
            // Debounce the overflow check
            clearTimeout(window.pageCheckTimeout);
            window.pageCheckTimeout = setTimeout(() => {
                checkPageOverflow();
            }, 100);
        }

        // Handle key events for page navigation
        function handleKeyDown(event) {
            if (event.key === 'Enter') {
                // Small delay to let the content update first
                setTimeout(() => {
                    checkPageOverflow();
                }, 10);
            }
        }

        // Check if current page content exceeds maximum height
        function checkPageOverflow() {
            const currentPage = getCurrentPage();
            if (!currentPage) return;

            const pageContent = currentPage.querySelector('.editor');
            if (!pageContent) return;

            const contentHeight = pageContent.scrollHeight;
            
            // If content exceeds max height, create new page
            if (contentHeight > MAX_PAGE_HEIGHT) {
                createNewPage();
                moveOverflowContent();
            }
        }

        // Get the currently active page
        function getCurrentPage() {
            return document.getElementById(`page-${currentPageCount}`);
        }

        // Create a new page
        function createNewPage() {
            currentPageCount++;
            const pageWrapper = document.getElementById('pageWrapper');
            
            const newPage = document.createElement('div');
            newPage.className = 'document-page';
            newPage.id = `page-${currentPageCount}`;
            
            newPage.innerHTML = `
                <div class="editor" id="editor-${currentPageCount}" contenteditable="true" 
                     oninput="handleInput(event)" 
                     onkeyup="updateFormatButtons()"
                     onmouseup="updateFormatButtons()"
                     onkeydown="handleKeyDown(event)">
                </div>
                <div class="page-number">Page ${currentPageCount}</div>
            `;
            
            pageWrapper.appendChild(newPage);
            
            // Focus on the new page
            const newEditor = document.getElementById(`editor-${currentPageCount}`);
            newEditor.focus();
            
            // Place cursor at the beginning
            const range = document.createRange();
            range.setStart(newEditor, 0);
            range.collapse(true);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }

        // Move overflow content to new page
        function moveOverflowContent() {
            const currentPage = getCurrentPage();
            const currentEditor = currentPage.querySelector('.editor');
            const newEditor = document.getElementById(`editor-${currentPageCount}`);
            
            if (!currentEditor || !newEditor) return;

            // Get all paragraphs from current page
            const paragraphs = Array.from(currentEditor.querySelectorAll('p, div, h1, h2, h3, h4, h5, h6, ul, ol, blockquote'));
            
            let totalHeight = 0;
            let splitIndex = paragraphs.length;

            // Find where to split content
            for (let i = 0; i < paragraphs.length; i++) {
                const element = paragraphs[i];
                const elementHeight = element.offsetHeight;
                
                if (totalHeight + elementHeight > MAX_PAGE_HEIGHT) {
                    splitIndex = Math.max(1, i); // Ensure at least one element stays
                    break;
                }
                totalHeight += elementHeight;
            }

            // Move elements to new page
            for (let i = splitIndex; i < paragraphs.length; i++) {
                const element = paragraphs[i];
                newEditor.appendChild(element);
            }

            // If new editor is empty, add a paragraph
            if (newEditor.children.length === 0) {
                newEditor.innerHTML = '<p><br></p>';
            }
        }

        // Document management
        function newDocument() {
            const newDoc = {
                title: 'Untitled Document',
                content: '<p>Start typing your document here...</p>',
                id: Date.now()
            };
            documents.push(newDoc);
            currentDocument = newDoc;
            document.getElementById('documentTitle').value = newDoc.title;
            document.getElementById('editor').innerHTML = newDoc.content;
            updateDocumentList();
            updateWordCount();
        }

        // Save document content from all pages
        function saveDocument() {
            let allContent = '';
            
            // Collect content from all pages
            for (let i = 1; i <= currentPageCount; i++) {
                const editor = document.getElementById(`editor-${i}`) || document.getElementById('editor');
                if (editor) {
                    allContent += editor.innerHTML;
                }
            }
            
            currentDocument.content = allContent;
            currentDocument.title = document.getElementById('documentTitle').value;
            
            // Save to localStorage
            localStorage.setItem('docuclone_documents', JSON.stringify(documents));
            
            // Show save confirmation
            showNotification('Document saved successfully!');
        }

        // Auto-save functionality
        function autoSave() {
            if (currentDocument) {
                let allContent = '';
                
                // Collect content from all pages
                for (let i = 1; i <= currentPageCount; i++) {
                    const editor = document.getElementById(`editor-${i}`) || document.getElementById('editor');
                    if (editor) {
                        allContent += editor.innerHTML;
                    }
                }
                
                currentDocument.content = allContent;
                currentDocument.title = document.getElementById('documentTitle').value;
                localStorage.setItem('docuclone_documents', JSON.stringify(documents));
            }
        }

        function loadDocuments() {
            const saved = localStorage.getItem('docuclone_documents');
            if (saved) {
                documents = JSON.parse(saved);
                if (documents.length > 0) {
                    currentDocument = documents[0];
                    document.getElementById('documentTitle').value = currentDocument.title;
                    document.getElementById('editor').innerHTML = currentDocument.content;
                }
                updateDocumentList();
            }
        }

        function updateDocumentList() {
            const list = document.getElementById('documentList');
            list.innerHTML = '';
            documents.forEach((doc, index) => {
                const item = document.createElement('li');
                item.className = 'document-item' + (doc.id === currentDocument.id ? ' active' : '');
                item.innerHTML = `
                    <i class="fas fa-file-alt"></i>
                    <span>${doc.title}</span>
                `;
                item.onclick = () => switchDocument(index);
                list.appendChild(item);
            });
        }

        function switchDocument(index) {
            currentDocument = documents[index];
            document.getElementById('documentTitle').value = currentDocument.title;
            document.getElementById('editor').innerHTML = currentDocument.content;
            updateDocumentList();
            updateWordCount();
        }

        function openDocument() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.html,.txt,.docx';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const content = e.target.result;
                        const newDoc = {
                            title: file.name.replace(/\.[^/.]+$/, ""),
                            content: content,
                            id: Date.now()
                        };
                        documents.push(newDoc);
                        currentDocument = newDoc;
                        document.getElementById('documentTitle').value = newDoc.title;
                        document.getElementById('editor').innerHTML = newDoc.content;
                        updateDocumentList();
                        updateWordCount();
                    };
                    reader.readAsText(file);
                }
            };
            input.click();
        }

        // Print document with proper page breaks
        function printDocument() {
            const printWindow = window.open('', '', 'width=800,height=600');
            let allContent = '';
            
            // Collect content from all pages with page breaks
            for (let i = 1; i <= currentPageCount; i++) {
                const editor = document.getElementById(`editor-${i}`) || document.getElementById('editor');
                if (editor) {
                    allContent += editor.innerHTML;
                    if (i < currentPageCount) {
                        allContent += '<div style="page-break-after: always;"></div>';
                    }
                }
            }
            
            printWindow.document.write(`
                <html>
                <head>
                    <title>${currentDocument.title}</title>
                    <style>
                        body { 
                            font-family: 'Times New Roman', serif; 
                            font-size: 11pt; 
                            line-height: 1.6; 
                            margin: 72px; 
                        }
                        @media print {
                            body { margin: 0.5in; }
                            .page-break { page-break-after: always; }
                        }
                    </style>
                </head>
                <body>${allContent}</body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Text formatting
        function formatText(command, value = null) {
            document.execCommand(command, false, value);
            document.getElementById('editor').focus();
            updateFormatButtons();
        }

        function changeFontFamily() {
            const fontFamily = document.getElementById('fontFamily').value;
            formatText('fontName', fontFamily);
        }

        function changeFontSize() {
            const fontSize = document.getElementById('fontSize').value;
            formatText('fontSize', fontSize);
        }

        function updateFormatButtons() {
            const buttons = {
                'boldBtn': 'bold',
                'italicBtn': 'italic',
                'underlineBtn': 'underline'
            };
            
            for (const [btnId, command] of Object.entries(buttons)) {
                const btn = document.getElementById(btnId);
                if (document.queryCommandState(command)) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            }
        }

        // Color picker functionality
        function initializeColorPickers() {
            const colors = [
                '#000000', '#434343', '#666666', '#999999', '#B7B7B7', '#CCCCCC', '#D9D9D9', '#EFEFEF',
                '#F3F3F3', '#FFFFFF', '#FF0000', '#FF9900', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF',
                '#9900FF', '#FF00FF', '#F4CCCC', '#FCE5CD', '#FFF2CC', '#D9EAD3', '#D0E0E3', '#C9DAF8',
                '#D9D2E9', '#EAD1DC', '#EA9999', '#F9CB9C', '#FFE599', '#B6D7A8', '#A2C4C9', '#A4C2F4'
            ];

            ['textColorGrid', 'highlightColorGrid'].forEach(gridId => {
                const grid = document.getElementById(gridId);
                colors.forEach(color => {
                    const colorOption = document.createElement('div');
                    colorOption.className = 'color-option';
                    colorOption.style.backgroundColor = color;
                    colorOption.onclick = () => applyColor(gridId.includes('text') ? 'foreColor' : 'hiliteColor', color);
                    grid.appendChild(colorOption);
                });
            });
        }

        function toggleColorPicker(type) {
            const paletteId = type + 'Palette';
            const palette = document.getElementById(paletteId);
            
            // Close other palettes
            document.querySelectorAll('.color-palette').forEach(p => {
                if (p.id !== paletteId) p.classList.remove('active');
            });
            
            palette.classList.toggle('active');
        }

        function applyColor(command, color) {
            formatText(command, color);
            document.querySelectorAll('.color-palette').forEach(p => p.classList.remove('active'));
        }

        // Insert functionality
        function insertLink() {
            const selection = window.getSelection();
            const selectedText = selection.toString();
            document.getElementById('linkText').value = selectedText;
            document.getElementById('linkModal').classList.add('active');
        }

        function insertLinkConfirm() {
            const text = document.getElementById('linkText').value;
            const url = document.getElementById('linkUrl').value;
            
            if (text && url) {
                const link = `<a href="${url}" target="_blank">${text}</a>`;
                formatText('insertHTML', link);
            }
            
            closeModal('linkModal');
        }

        function insertImage() {
            document.getElementById('imageModal').classList.add('active');
        }

        function insertImageConfirm() {
            const url = document.getElementById('imageUrl').value;
            const alt = document.getElementById('imageAlt').value;
            
            if (url) {
                const img = `<img src="${url}" alt="${alt}" style="max-width: 100%; height: auto;">`;
                formatText('insertHTML', img);
            }
            
            closeModal('imageModal');
        }

        function insertTable() {
            document.getElementById('tableModal').classList.add('active');
        }

        function insertTableConfirm() {
            const rows = parseInt(document.getElementById('tableRows').value);
            const cols = parseInt(document.getElementById('tableCols').value);
            
            let tableHTML = '<table border="1" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
            
            for (let i = 0; i < rows; i++) {
                tableHTML += '<tr>';
                for (let j = 0; j < cols; j++) {
                    tableHTML += '<td style="padding: 8px; border: 1px solid #ddd;">&nbsp;</td>';
                }
                tableHTML += '</tr>';
            }
            
            tableHTML += '</table>';
            formatText('insertHTML', tableHTML);
            closeModal('tableModal');
        }

        // Modal management
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Undo/Redo functionality
        function undo() {
            formatText('undo');
        }

        function redo() {
            formatText('redo');
        }

        // Zoom functionality
        function zoomIn() {
            if (zoomLevel < 200) {
                zoomLevel += 10;
                applyZoom();
            }
        }

        function zoomOut() {
            if (zoomLevel > 50) {
                zoomLevel -= 10;
                applyZoom();
            }
        }

        function applyZoom() {
            document.getElementById('editor').style.fontSize = (11 * zoomLevel / 100) + 'pt';
            document.getElementById('zoomLevel').textContent = zoomLevel + '%';
        }

        // Update word count from all pages
        function updateWordCount() {
            let totalText = '';
            
            // Collect text from all pages
            for (let i = 1; i <= currentPageCount; i++) {
                const editor = document.getElementById(`editor-${i}`) || document.getElementById('editor');
                if (editor) {
                    const text = editor.innerText || editor.textContent || '';
                    totalText += text + ' ';
                }
            }
            
            const words = totalText.trim().split(/\s+/).filter(word => word.length > 0);
            document.getElementById('wordCount').textContent = words.length + ' words';
        }

        // Comments functionality
        function showComments() {
            const panel = document.getElementById('commentPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }

        // Share functionality
        function shareDocument() {
            const shareData = {
                title: currentDocument.title,
                text: 'Check out this document I created!',
                url: window.location.href
            };
            
            if (navigator.share) {
                navigator.share(shareData);
            } else {
                // Fallback - copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showNotification('Document link copied to clipboard!');
                });
            }
        }

        // Template loading
        function loadTemplate(templateType) {
            let content = '';
            let title = '';
            
            switch (templateType) {
                case 'blank':
                    content = '<p>Start typing your document here...</p>';
                    title = 'Untitled Document';
                    break;
                case 'resume':
                    content = `
                        <h1>Your Name</h1>
                        <p>Email: your.email@example.com | Phone: (555) 123-4567</p>
                        <hr>
                        <h2>Professional Summary</h2>
                        <p>Write a brief summary of your professional background and key skills.</p>
                        <h2>Experience</h2>
                        <h3>Job Title - Company Name</h3>
                        <p><em>Start Date - End Date</em></p>
                        <ul>
                            <li>Key achievement or responsibility</li>
                            <li>Another achievement or responsibility</li>
                        </ul>
                        <h2>Education</h2>
                        <h3>Degree - Institution Name</h3>
                        <p><em>Graduation Year</em></p>
                        <h2>Skills</h2>
                        <ul>
                            <li>Skill 1</li>
                            <li>Skill 2</li>
                            <li>Skill 3</li>
                        </ul>
                    `;
                    title = 'Resume';
                    break;
                case 'letter':
                    content = `
                        <p style="text-align: right;">[Your Address]<br>
                        [City, State ZIP Code]<br>
                        [Date]</p>
                        <p>[Recipient Name]<br>
                        [Recipient Title]<br>
                        [Company/Organization]<br>
                        [Address]</p>
                        <p>Dear [Recipient Name],</p>
                        <p>Write your letter content here. This is the first paragraph where you introduce yourself and the purpose of your letter.</p>
                        <p>This is the second paragraph where you can provide more details or supporting information.</p>
                        <p>This is the closing paragraph where you summarize your main points and indicate next steps.</p>
                        <p>Sincerely,<br><br>
                        [Your Name]</p>
                    `;
                    title = 'Letter';
                    break;
            }
            
            const newDoc = {
                title: title,
                content: content,
                id: Date.now()
            };
            
            documents.push(newDoc);
            currentDocument = newDoc;
            document.getElementById('documentTitle').value = newDoc.title;
            document.getElementById('editor').innerHTML = newDoc.content;
            updateDocumentList();
            updateWordCount();
        }

        // Notification system
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 12px 24px;
                border-radius: 4px;
                z-index: 3000;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 's':
                        e.preventDefault();
                        saveDocument();
                        break;
                    case 'n':
                        e.preventDefault();
                        newDocument();
                        break;
                    case 'p':
                        e.preventDefault();
                        printDocument();
                        break;
                    case 'b':
                        e.preventDefault();
                        formatText('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        formatText('italic');
                        break;
                    case 'u':
                        e.preventDefault();
                        formatText('underline');
                        break;
                }
            }
        });

        // Close color pickers when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.color-picker')) {
                document.querySelectorAll('.color-palette').forEach(p => p.classList.remove('active'));
            }
        });

        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });
    </script>
</body>
</html>