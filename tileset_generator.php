<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Auto-Flow Tileset Generator</title>
    <style>
        /* Base styles */
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f9; display: flex; gap: 20px; height: 100vh; box-sizing: border-box; overflow: hidden; }
        
        /* Sidebar Layout */
        .sidebar { width: 450px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; height: 100%; flex-shrink: 0; }
        
        /* Tabs UI */
        .tabs-header { display: flex; border-bottom: 1px solid #ccc; background: #f4f4f9; border-radius: 8px 8px 0 0; flex-shrink: 0; }
        .tab-btn { flex: 1; padding: 15px; border: none; background: transparent; cursor: pointer; font-weight: bold; font-size: 14px; color: #555; border-bottom: 3px solid transparent; transition: 0.3s; outline: none; }
        .tab-btn.active { color: #007bff; border-bottom-color: #007bff; background: #fff; }
        .tab-btn:hover:not(.active) { background: #e9ecef; }
        
        .tab-content { display: none; flex: 1; overflow-y: auto; padding: 20px; flex-direction: column; background: #fafafa; border-radius: 0 0 8px 8px; scroll-behavior: smooth; }
        .tab-content.active { display: block; } /* Show active tab */
        #tab-list { padding: 20px; } /* Specific padding for list tab */

        .main-content { flex: 1; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: center; overflow: auto; height: 100%; box-sizing: border-box; }
        h2, h3 { margin-top: 0; color: #333; margin-bottom: 15px; }
        
        /* Form inputs in grid */
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: bold; color: #444; }
        .form-group input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        
        /* Buttons */
        .btn-group { display: flex; flex-direction: column; gap: 15px; }
        .btn-row { display: flex; gap: 10px; flex-wrap: wrap; }
        button { flex: 1; background: #007bff; color: white; border: none; padding: 12px 10px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; min-width: 100px; transition: 0.2s;}
        button:hover { background: #0056b3; }
        button.success { background: #28a745; }
        button.success:hover { background: #218838; }
        button.warning { background: #ffc107; color: #333; }
        button.warning:hover { background: #e0a800; }
        button.info { background: #17a2b8; }
        button.info:hover { background: #138496; }
        button.dark { background: #343a40; }
        button.dark:hover { background: #23272b; }
        button.danger { background: #dc3545; color: white; padding: 5px 8px; font-size: 12px; flex: none; min-width: auto; }
        button.danger:hover { background: #c82333; }
        button.action-btn { background: #6c757d; padding: 5px 8px; font-size: 12px; flex: none; min-width: auto; }
        button.action-btn:hover { background: #5a6268; }
        button.clone-btn { background: #8e44ad; padding: 5px 8px; font-size: 12px; flex: none; min-width: auto; color: white;}
        button.clone-btn:hover { background: #732d91; }

        /* Image List Items */
        .image-item { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; border-radius: 6px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: 0.3s; }
        /* Selected state for item */
        .image-item.selected { border: 2px solid #007bff; background: #eef6ff; box-shadow: 0 0 8px rgba(0,123,255,0.4); transform: scale(1.02); z-index: 2; position: relative; }
        .image-item.out-of-bounds { border-color: #dc3545; background: #ffe6e6; }
        .image-item-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; gap: 10px; }
        .item-info { display: flex; align-items: center; gap: 10px; flex: 1; overflow: hidden; cursor: pointer; }
        .item-info img { max-width: 40px; max-height: 40px; border-radius: 4px; }
        .item-info .empty-box { width: 40px; height: 40px; border: 2px dashed #999; background: #eee; border-radius: 4px; }
        .item-name { font-size: 13px; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        .item-controls-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: #f9f9f9; padding: 8px; border-radius: 6px; }
        .image-item.selected .item-controls-grid { background: #e0edfb; }
        .item-controls-grid > div { display: flex; flex-direction: column; }
        .item-controls-grid label { font-size: 11px; margin-bottom: 4px; font-weight: bold; color: #555; }
        .item-controls-grid select, .item-controls-grid input { width: 100%; padding: 4px; font-size: 12px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 3px; }
        
        .move-controls { display: flex; gap: 4px; }
        .error-text { color: #dc3545; font-size: 12px; font-weight: bold; margin-top: 5px; display: none; }
        .out-of-bounds .error-text { display: block; }

        /* Canvas */
        canvas { border: 2px dashed #aaa; max-width: 100%; background: transparent; box-shadow: 0 4px 15px rgba(0,0,0,0.1); cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <!-- Tabs Header -->
        <div class="tabs-header">
            <button id="btn-tab-settings" class="tab-btn active" onclick="switchTab('tab-settings')">⚙️ Settings</button>
            <button id="btn-tab-list" class="tab-btn" onclick="switchTab('tab-list')">📋 Items List</button>
        </div>

        <!-- Tab 1: Settings Content -->
        <div id="tab-settings" class="tab-content active">
            <h2>Global Settings</h2>
            
            <div class="settings-grid">
                <div class="form-group">
                    <label>Columns</label>
                    <input type="number" id="g-cols" value="15" min="1" onchange="updateSystem()">
                </div>
                <div class="form-group">
                    <label>Rows</label>
                    <input type="number" id="g-rows" value="10" min="1" onchange="updateSystem()">
                </div>
                <div class="form-group">
                    <label>Cell Width (px)</label>
                    <input type="number" id="g-cellW" value="64" min="10" onchange="updateSystem()">
                </div>
                <div class="form-group">
                    <label>Cell Height (px)</label>
                    <input type="number" id="g-cellH" value="64" min="10" onchange="updateSystem()">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Gap / Spacing (px)</label>
                    <input type="number" id="g-gap" value="0" min="0" onchange="updateSystem()">
                </div>
            </div>

            <div class="btn-group">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-size:13px;">Add Images</label>
                    <input type="file" id="file-input" multiple accept="image/*" style="width: 100%; font-size: 13px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div class="btn-row">
                    <button class="warning" onclick="addEmptyCell()">+ Empty Spacer</button>
                </div>
                <div class="btn-row">
                    <button class="info" id="grid-toggle-btn" onclick="toggleGrid()">Hide Grid</button>
                    <button class="success" onclick="downloadCanvas()">Export PNG</button>
                </div>
                <div class="btn-row">
                    <button class="dark" onclick="saveProject()">💾 Save Project</button>
                    <button class="dark" onclick="document.getElementById('load-project-input').click()">📂 Load Project</button>
                    <input type="file" id="load-project-input" accept=".json" style="display: none;">
                </div>
            </div>
        </div>

        <!-- Tab 2: Items List Content -->
        <div id="tab-list" class="tab-content">
            <!-- Items injected via JS -->
            <div id="image-list"></div>
        </div>
    </div>

    <div class="main-content">
        <h2>Live Preview <small style="font-size: 12px; color: #666; font-weight: normal;">(Click item to select & edit)</small></h2>
        <canvas id="canvas"></canvas>
    </div>

    <script>
        // Array to store configurations
        let itemsData = [];
        let itemCounter = 0;
        let showGrid = true; 
        let selectedItemId = null; // Track currently selected item

        // Switch between Settings and List Tabs
        function switchTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            // Remove active state from all tab buttons
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show target tab
            document.getElementById(tabId).classList.add('active');
            // Highlight target button
            document.getElementById('btn-' + tabId).classList.add('active');
        }

        // Listen for image uploads
        document.getElementById('file-input').addEventListener('change', function(e) {
            const files = e.target.files;
            if (!files.length) return;

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = new Image();
                    img.onload = function() {
                        itemsData.push({
                            id: itemCounter++,
                            type: 'image',
                            imgElement: img,
                            name: file.name,
                            colSpan: 1,
                            rowSpan: 1,
                            offsetX: 0,
                            offsetY: 0,
                            scaleX: 1.0,
                            scaleY: 1.0,
                            fitMode: 'contain',
                            outOfBounds: false,
                            calcX: 0, 
                            calcY: 0
                        });
                        updateSystem();
                    }
                    img.src = event.target.result;
                }
                reader.readAsDataURL(file);
            });
            e.target.value = ''; 
        });

        // Handle project saving
        function saveProject() {
            const projectData = {
                settings: {
                    cols: document.getElementById('g-cols').value,
                    rows: document.getElementById('g-rows').value,
                    cellW: document.getElementById('g-cellW').value,
                    cellH: document.getElementById('g-cellH').value,
                    gap: document.getElementById('g-gap').value,
                    showGrid: showGrid
                },
                items: itemsData.map(item => ({
                    id: item.id,
                    type: item.type,
                    name: item.name,
                    colSpan: item.colSpan,
                    rowSpan: item.rowSpan,
                    offsetX: item.offsetX,
                    offsetY: item.offsetY,
                    scaleX: item.scaleX,
                    scaleY: item.scaleY,
                    fitMode: item.fitMode,
                    src: item.type === 'image' ? item.imgElement.src : null 
                }))
            };

            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(projectData));
            const dlAnchorElem = document.createElement('a');
            dlAnchorElem.setAttribute("href", dataStr);
            dlAnchorElem.setAttribute("download", "tileset_project.json");
            dlAnchorElem.click();
        }

        // Handle project loading
        document.getElementById('load-project-input').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = async function(event) {
                try {
                    const projectData = JSON.parse(event.target.result);
                    
                    document.getElementById('g-cols').value = projectData.settings.cols;
                    document.getElementById('g-rows').value = projectData.settings.rows;
                    document.getElementById('g-cellW').value = projectData.settings.cellW;
                    document.getElementById('g-cellH').value = projectData.settings.cellH;
                    document.getElementById('g-gap').value = projectData.settings.gap;
                    showGrid = projectData.settings.showGrid !== undefined ? projectData.settings.showGrid : true;
                    document.getElementById('grid-toggle-btn').innerText = showGrid ? 'Hide Grid' : 'Show Grid';

                    let maxId = 0;
                    selectedItemId = null;

                    const loadedItems = await Promise.all(projectData.items.map(item => {
                        if (item.id >= maxId) maxId = item.id + 1;
                        
                        return new Promise(resolve => {
                            if (item.type === 'empty') {
                                resolve({ ...item, calcX: 0, calcY: 0, outOfBounds: false });
                            } else {
                                const img = new Image();
                                img.onload = () => {
                                    resolve({ ...item, imgElement: img, calcX: 0, calcY: 0, outOfBounds: false });
                                };
                                img.src = item.src;
                            }
                        });
                    }));

                    itemsData = loadedItems;
                    itemCounter = maxId;
                    updateSystem();

                } catch (error) {
                    alert("Error loading project file. Make sure it's a valid JSON generated by this app.");
                    console.error(error);
                }
            };
            reader.readAsText(file);
            e.target.value = ''; 
        });

        // Add a blank spacer item
        function addEmptyCell() {
            itemsData.push({
                id: itemCounter++,
                type: 'empty',
                name: 'Empty Spacer',
                colSpan: 1,
                rowSpan: 1,
                offsetX: 0,
                offsetY: 0,
                scaleX: 1.0,
                scaleY: 1.0,
                outOfBounds: false,
                calcX: 0,
                calcY: 0
            });
            updateSystem();
        }

        // Toggle visual grid display
        function toggleGrid() {
            showGrid = !showGrid;
            document.getElementById('grid-toggle-btn').innerText = showGrid ? 'Hide Grid' : 'Show Grid';
            drawCanvas();
        }

        // Clone an existing item entirely
        function cloneItem(index) {
            const original = itemsData[index];
            const clone = {
                ...original,
                id: itemCounter++
            };
            itemsData.splice(index + 1, 0, clone);
            updateSystem();
        }

        // Move item up
        function moveUp(index) {
            if (index > 0) {
                const temp = itemsData[index];
                itemsData[index] = itemsData[index - 1];
                itemsData[index - 1] = temp;
                updateSystem();
            }
        }

        // Move item down
        function moveDown(index) {
            if (index < itemsData.length - 1) {
                const temp = itemsData[index];
                itemsData[index] = itemsData[index + 1];
                itemsData[index + 1] = temp;
                updateSystem();
            }
        }

        // Remove item
        function removeItem(id) {
            itemsData = itemsData.filter(item => item.id !== id);
            if(selectedItemId === id) selectedItemId = null;
            updateSystem();
        }

        // Handle selection via click
        function selectItem(id) {
            selectedItemId = id;
            renderListUI();
            const el = document.getElementById(`item-container-${id}`);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Handle dynamically changing values
        function updateItem(index, key, value) {
            if (key === 'fitMode') {
                itemsData[index][key] = value;
            } else if (key === 'scaleX' || key === 'scaleY' || key === 'offsetX' || key === 'offsetY') {
                itemsData[index][key] = parseFloat(value) || 0;
            } else {
                let parsed = parseInt(value) || 1;
                if(parsed < 1) parsed = 1;
                itemsData[index][key] = parsed;
            }
            updateSystem();
        }

        // Master controller function
        function updateSystem() {
            calculateAutoLayout();
            renderListUI();
            drawCanvas();
        }

        // Auto-Flow Grid Algorithm
        function calculateAutoLayout() {
            const cols = parseInt(document.getElementById('g-cols').value) || 1;
            const rows = parseInt(document.getElementById('g-rows').value) || 1;
            
            let grid = Array.from({length: rows}, () => Array(cols).fill(false));

            itemsData.forEach(item => {
                item.outOfBounds = true; 
                let placed = false;

                for (let y = 0; y < rows; y++) {
                    for (let x = 0; x < cols; x++) {
                        if (canFit(grid, x, y, item.colSpan, item.rowSpan, cols, rows)) {
                            markOccupied(grid, x, y, item.colSpan, item.rowSpan);
                            item.calcX = x;
                            item.calcY = y;
                            item.outOfBounds = false;
                            placed = true;
                            break;
                        }
                    }
                    if (placed) break;
                }
            });
        }

        // Checks collision & boundaries
        function canFit(grid, x, y, w, h, maxCols, maxRows) {
            if (x + w > maxCols || y + h > maxRows) return false;
            for (let i = y; i < y + h; i++) {
                for (let j = x; j < x + w; j++) {
                    if (grid[i][j]) return false;
                }
            }
            return true;
        }

        // Flags grid indices as occupied
        function markOccupied(grid, x, y, w, h) {
            for (let i = y; i < y + h; i++) {
                for (let j = x; j < x + w; j++) {
                    grid[i][j] = true;
                }
            }
        }

        // Canvas Click Event - Selects item visually and switches tab
        document.getElementById('canvas').addEventListener('mousedown', function(e) {
            const canvas = document.getElementById('canvas');
            const rect = canvas.getBoundingClientRect();
            
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (e.clientX - rect.left) * scaleX;
            const y = (e.clientY - rect.top) * scaleY;

            const cellW = parseInt(document.getElementById('g-cellW').value) || 128;
            const cellH = parseInt(document.getElementById('g-cellH').value) || 128;
            const gap = parseInt(document.getElementById('g-gap').value) || 0;

            let clickedId = null;

            for (let item of itemsData) {
                if (item.outOfBounds) continue;

                let startX = (item.calcX * cellW) + (item.calcX * gap);
                let startY = (item.calcY * cellH) + (item.calcY * gap);
                let endX = startX + (item.colSpan * cellW) + ((item.colSpan - 1) * gap);
                let endY = startY + (item.rowSpan * cellH) + ((item.rowSpan - 1) * gap);

                if (x >= startX && x <= endX && y >= startY && y <= endY) {
                    clickedId = item.id;
                    break;
                }
            }

            if (clickedId !== null) {
                // Switch to List Tab automatically
                switchTab('tab-list');
                
                // Allow a tiny delay for DOM to apply display:block before calculating scroll position
                setTimeout(() => {
                    selectItem(clickedId);
                }, 50);
                
            } else {
                selectedItemId = null;
                renderListUI();
            }
        });

        // DOM rendering for Sidebar items
        function renderListUI() {
            const listContainer = document.getElementById('image-list');
            listContainer.innerHTML = '';

            if(itemsData.length === 0) {
                listContainer.innerHTML = '<p style="color:#777; text-align:center; font-size:14px; margin-top:40px;">No items added yet.<br><br>Go to Settings to add images.</p>';
                return;
            }

            itemsData.forEach((item, index) => {
                const div = document.createElement('div');
                div.id = `item-container-${item.id}`;
                
                let classes = ['image-item'];
                if (item.outOfBounds) classes.push('out-of-bounds');
                if (item.id === selectedItemId) classes.push('selected');
                div.className = classes.join(' ');
                
                const thumb = item.type === 'image' 
                    ? `<img src="${item.imgElement.src}" alt="thumb">`
                    : `<div class="empty-box"></div>`;

                const fitModeSelect = item.type === 'image' ? `
                    <div style="grid-column: span 2;">
                        <label>Fit Mode</label>
                        <select onchange="updateItem(${index}, 'fitMode', this.value)">
                            <option value="stretch" ${item.fitMode === 'stretch' ? 'selected' : ''}>Stretch</option>
                            <option value="contain" ${item.fitMode === 'contain' ? 'selected' : ''}>Contain (Keep Aspect)</option>
                        </select>
                    </div>
                ` : '';

                div.innerHTML = `
                    <div class="image-item-header">
                        <div class="item-info" onclick="selectItem(${item.id})">
                            ${thumb}
                            <span class="item-name" title="${item.name}">${item.name}</span>
                        </div>
                        <div class="move-controls">
                            <button class="action-btn" onclick="moveUp(${index})">▲</button>
                            <button class="action-btn" onclick="moveDown(${index})">▼</button>
                            <button class="clone-btn" onclick="cloneItem(${index})">Copy</button>
                            <button class="danger" onclick="removeItem(${item.id})">✖</button>
                        </div>
                    </div>
                    <div class="item-controls-grid">
                        <div>
                            <label>Col Span</label>
                            <input type="number" min="1" value="${item.colSpan}" onchange="updateItem(${index}, 'colSpan', this.value)">
                        </div>
                        <div>
                            <label>Row Span</label>
                            <input type="number" min="1" value="${item.rowSpan}" onchange="updateItem(${index}, 'rowSpan', this.value)">
                        </div>
                        <div>
                            <label>Offset X (px)</label>
                            <input type="number" step="1" value="${item.offsetX}" onchange="updateItem(${index}, 'offsetX', this.value)">
                        </div>
                        <div>
                            <label>Offset Y (px)</label>
                            <input type="number" step="1" value="${item.offsetY}" onchange="updateItem(${index}, 'offsetY', this.value)">
                        </div>
                        <div>
                            <label>Scale X (1 = 100%)</label>
                            <input type="number" step="0.1" value="${item.scaleX}" onchange="updateItem(${index}, 'scaleX', this.value)">
                        </div>
                        <div>
                            <label>Scale Y (1 = 100%)</label>
                            <input type="number" step="0.1" value="${item.scaleY}" onchange="updateItem(${index}, 'scaleY', this.value)">
                        </div>
                        ${fitModeSelect}
                    </div>
                    <div class="error-text">⚠️ Does not fit in grid! Pushed out of bounds.</div>
                `;
                listContainer.appendChild(div);
            });
        }

        // Draw routine mapping memory to HTML5 Canvas
        function drawCanvas() {
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');

            const cols = parseInt(document.getElementById('g-cols').value) || 1;
            const rows = parseInt(document.getElementById('g-rows').value) || 1;
            const cellW = parseInt(document.getElementById('g-cellW').value) || 128;
            const cellH = parseInt(document.getElementById('g-cellH').value) || 128;
            const gap = parseInt(document.getElementById('g-gap').value) || 0;

            canvas.width = (cols * cellW) + ((cols - 1) * gap);
            canvas.height = (rows * cellH) + ((rows - 1) * gap);

            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (showGrid) {
                ctx.strokeStyle = 'rgba(0, 0, 0, 0.2)';
                ctx.lineWidth = 1;
                ctx.setLineDash([4, 4]); 
                for (let r = 0; r < rows; r++) {
                    for (let c = 0; c < cols; c++) {
                        let cx = (c * cellW) + (c * gap);
                        let cy = (r * cellH) + (r * gap);
                        ctx.strokeRect(cx, cy, cellW, cellH);
                    }
                }
                ctx.setLineDash([]); 
            }

            itemsData.forEach(item => {
                if (item.outOfBounds) return;

                const drawX = (item.calcX * cellW) + (item.calcX * gap);
                const drawY = (item.calcY * cellH) + (item.calcY * gap);
                
                const targetW = (item.colSpan * cellW) + ((item.colSpan - 1) * gap);
                const targetH = (item.rowSpan * cellH) + ((item.rowSpan - 1) * gap);

                if (item.id === selectedItemId && showGrid) {
                    ctx.fillStyle = 'rgba(0, 123, 255, 0.1)';
                    ctx.fillRect(drawX, drawY, targetW, targetH);
                }

                if (item.type === 'image') {
                    ctx.save();
                    
                    const centerX = drawX + (targetW / 2);
                    const centerY = drawY + (targetH / 2);
                    
                    ctx.translate(centerX + item.offsetX, centerY + item.offsetY);
                    ctx.scale(item.scaleX, item.scaleY);

                    let renderW = targetW;
                    let renderH = targetH;

                    if (item.fitMode === 'contain') {
                        const imgRatio = item.imgElement.width / item.imgElement.height;
                        const targetRatio = targetW / targetH;

                        if (imgRatio > targetRatio) {
                            renderW = targetW;
                            renderH = targetW / imgRatio;
                        } else {
                            renderH = targetH;
                            renderW = targetH * imgRatio;
                        }
                    }

                    ctx.drawImage(
                        item.imgElement, 
                        -renderW / 2, 
                        -renderH / 2, 
                        renderW, 
                        renderH
                    );
                    
                    ctx.restore();
                } 
            });
        }

        // Export Canvas to PNG
        function downloadCanvas() {
            const wasGridShowing = showGrid;
            if (showGrid) {
                showGrid = false;
                drawCanvas(); 
            }

            const canvas = document.getElementById('canvas');
            const link = document.createElement('a');
            link.download = 'tileset_autoflow_generated.png';
            link.href = canvas.toDataURL('image/png');
            link.click();

            if (wasGridShowing) {
                showGrid = true;
                drawCanvas(); 
            }
        }

        // Initial launch
        updateSystem();
    </script>
</body>
</html>
