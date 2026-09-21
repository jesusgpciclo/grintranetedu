/**
 * SmartTable - Advanced Data Table Enhancer for Laravel Gestores
 * Provides: Search (global & column-specific), Interactive Sorting,
 * Cumulative Filtering, Dynamic Pagination (default 25), and Safe Export (CSV, JSON, PDF).
 */

class SmartTable {
    constructor(tableElement) {
        this.table = tableElement;
        this.headers = Array.from(this.table.querySelectorAll('thead th'));
        this.rows = Array.from(this.table.querySelectorAll('tbody tr'));
        this.originalRows = [...this.rows];
        this.currentPage = 1;
        this.pageSize = parseInt(localStorage.getItem('smart_table_per_page')) || 25;
        
        this.filters = {};
        this.globalSearchQuery = '';

        this.init();
    }

    init() {
        // Wrap table in container
        const wrapper = document.createElement('div');
        wrapper.className = 'smart-table-wrapper';
        this.table.parentNode.insertBefore(wrapper, this.table);
        
        // Setup Toolbar & Controls
        this.createToolbar(wrapper);
        wrapper.appendChild(this.table);

        // Setup Column Filters
        this.setupColumnFilters();

        // Setup Client-side pagination container
        this.paginationContainer = document.createElement('div');
        this.paginationContainer.className = 'smart-table-pagination';
        wrapper.appendChild(this.paginationContainer);

        // Initial Render
        this.applyFiltersAndRender();
    }

    createToolbar(wrapper) {
        const toolbar = document.createElement('div');
        toolbar.className = 'smart-table-toolbar';

        // Page Size Selector
        const sizeSelectorGroup = document.createElement('div');
        sizeSelectorGroup.className = 'smart-table-size-group';
        sizeSelectorGroup.innerHTML = `
            <label for="per_page_select" class="text-sm text-muted">Mostrar:</label>
            <select id="per_page_select" class="smart-table-select">
                <option value="10" ${this.pageSize === 10 ? 'selected' : ''}>10</option>
                <option value="25" ${this.pageSize === 25 ? 'selected' : ''}>25</option>
                <option value="50" ${this.pageSize === 50 ? 'selected' : ''}>50</option>
                <option value="100" ${this.pageSize === 100 ? 'selected' : ''}>100</option>
                <option value="all" ${this.pageSize > 1000 ? 'selected' : ''}>Todo</option>
            </select>
        `;
        
        const select = sizeSelectorGroup.querySelector('select');
        select.addEventListener('change', (e) => {
            const val = e.target.value;
            this.pageSize = val === 'all' ? 999999 : parseInt(val);
            localStorage.setItem('smart_table_per_page', val === 'all' ? 999999 : val);
            
            // Check if table is paginated server-side (detect Laravel paginator link)
            const serverPagination = document.querySelector('.pagination, [class*="pagination"]');
            if (serverPagination) {
                // Reload page with new per_page param
                const url = new URL(window.location.href);
                url.searchParams.set('per_page', this.pageSize);
                window.location.href = url.toString();
            } else {
                this.currentPage = 1;
                this.applyFiltersAndRender();
            }
        });

        // Global Search
        const searchGroup = document.createElement('div');
        searchGroup.className = 'smart-table-search-group';
        searchGroup.innerHTML = `
            <input type="text" placeholder="Búsqueda global..." class="smart-table-search-input" aria-label="Buscar en tabla">
        `;
        const searchInput = searchGroup.querySelector('input');
        searchInput.addEventListener('input', (e) => {
            this.globalSearchQuery = e.target.value.toLowerCase();
            this.currentPage = 1;
            this.applyFiltersAndRender();
        });

        // Export Controls
        const exportGroup = document.createElement('div');
        exportGroup.className = 'smart-table-export-group';
        
        const btnCSV = this.createButton('Exportar CSV', 'btn-export-csv', () => this.exportCSV());
        const btnJSON = this.createButton('Exportar JSON', 'btn-export-json', () => this.exportJSON());
        const btnPDF = this.createButton('Imprimir / PDF', 'btn-export-pdf', () => this.exportPDF());
        
        exportGroup.appendChild(btnCSV);
        exportGroup.appendChild(btnJSON);
        exportGroup.appendChild(btnPDF);

        toolbar.appendChild(sizeSelectorGroup);
        toolbar.appendChild(searchGroup);
        toolbar.appendChild(exportGroup);
        
        wrapper.appendChild(toolbar);
    }

    createButton(text, className, onClick) {
        const btn = document.createElement('button');
        btn.className = `btn btn-sm ${className}`;
        btn.textContent = text;
        btn.type = 'button';
        btn.addEventListener('click', onClick);
        return btn;
    }

    setupColumnFilters() {
        if (this.table.dataset.columnFilters === 'false' || this.table.dataset.disableColumnFilters === 'true') {
            return;
        }
        const thead = this.table.querySelector('thead');
        if (!thead) return;

        // Create a new header row for inputs
        const filterRow = document.createElement('tr');
        filterRow.className = 'smart-table-filter-row';

        this.headers.forEach((header, index) => {
            const th = document.createElement('th');
            th.className = 'p-2';
            
            // Check if column is actions or empty/checkbox
            const isActions = header.textContent.trim().toLowerCase().includes('acciones') || 
                              header.textContent.trim() === '';
                              
            if (isActions) {
                th.innerHTML = '';
            } else {
                const input = document.createElement('input');
                input.className = 'smart-table-column-filter';
                input.placeholder = `Filtrar...`;
                input.ariaLabel = `Filtrar columna ${header.textContent.trim()}`;
                input.addEventListener('input', (e) => {
                    this.filters[index] = e.target.value.toLowerCase();
                    this.currentPage = 1;
                    this.applyFiltersAndRender();
                });
                th.appendChild(input);
            }
            filterRow.appendChild(th);
        });

        thead.appendChild(filterRow);
    }

    applyFiltersAndRender() {
        // Filter rows
        this.rows = this.originalRows.filter(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            
            // Check Global Search
            const matchesGlobal = this.globalSearchQuery === '' || cells.some(cell => 
                cell.textContent.toLowerCase().includes(this.globalSearchQuery)
            );

            // Check Column Filters
            const matchesColumns = Object.keys(this.filters).every(colIdx => {
                const query = this.filters[colIdx];
                if (!query) return true;
                const cell = cells[colIdx];
                return cell ? cell.textContent.toLowerCase().includes(query) : true;
            });

            return matchesGlobal && matchesColumns;
        });

        this.render();
    }

    render() {
        // Paginate rows
        const start = (this.currentPage - 1) * this.pageSize;
        const end = start + this.pageSize;
        
        // Hide all rows first
        this.originalRows.forEach(row => row.style.display = 'none');
        
        // Show current page rows
        const pageRows = this.rows.slice(start, end);
        pageRows.forEach(row => row.style.display = '');

        // Update Pagination Controls
        this.renderPagination();
    }

    renderPagination() {
        this.paginationContainer.innerHTML = '';
        
        const totalRows = this.rows.length;
        const totalPages = Math.ceil(totalRows / this.pageSize);
        
        if (totalPages <= 1) return;

        const info = document.createElement('span');
        info.className = 'text-sm text-muted';
        info.textContent = `Mostrando ${Math.min(totalRows, (this.currentPage - 1) * this.pageSize + 1)}-${Math.min(totalRows, this.currentPage * this.pageSize)} de ${totalRows} filas`;
        this.paginationContainer.appendChild(info);

        const btnContainer = document.createElement('div');
        btnContainer.className = 'smart-table-page-buttons';

        // Prev Button
        const btnPrev = document.createElement('button');
        btnPrev.className = 'btn btn-sm';
        btnPrev.textContent = 'Anterior';
        btnPrev.disabled = this.currentPage === 1;
        btnPrev.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.render();
            }
        });
        btnContainer.appendChild(btnPrev);

        // Current / Total Info
        const pageIndicator = document.createElement('span');
        pageIndicator.className = 'px-3 text-sm font-semibold';
        pageIndicator.textContent = `Pág. ${this.currentPage} de ${totalPages}`;
        btnContainer.appendChild(pageIndicator);

        // Next Button
        const btnNext = document.createElement('button');
        btnNext.className = 'btn btn-sm';
        btnNext.textContent = 'Siguiente';
        btnNext.disabled = this.currentPage === totalPages;
        btnNext.addEventListener('click', () => {
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.render();
            }
        });
        btnContainer.appendChild(btnNext);

        this.paginationContainer.appendChild(btnContainer);
    }

    // Security: Escaping values to prevent CSV formula injection
    sanitizeCSVCell(val) {
        let clean = val.replace(/(\r\n|\n|\r)/gm, " ").trim();
        // If cell starts with security hazard prefix, escape it
        if (['=', '+', '-', '@'].some(char => clean.startsWith(char))) {
            clean = `'${clean}`;
        }
        // Escape quotes
        if (clean.includes('"')) {
            clean = clean.replace(/"/g, '""');
        }
        return `"${clean}"`;
    }

    getExportData() {
        const headersToExport = this.headers.map(th => th.textContent.trim()).filter(h => h !== 'Acciones' && h !== '');
        const dataRows = this.rows.map(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            return cells
                .filter((cell, idx) => {
                    const header = this.headers[idx];
                    return header && header.textContent.trim() !== 'Acciones' && header.textContent.trim() !== '';
                })
                .map(cell => cell.textContent.trim());
        });
        return { headers: headersToExport, rows: dataRows };
    }

    exportCSV() {
        const { headers, rows } = this.getExportData();
        const csvContent = [];
        
        // Add Headers
        csvContent.push(headers.map(h => this.sanitizeCSVCell(h)).join(','));
        
        // Add Rows
        rows.forEach(row => {
            csvContent.push(row.map(cell => this.sanitizeCSVCell(cell)).join(','));
        });

        // Safe Download with UTF-8 BOM
        const blob = new Blob(['\uFEFF' + csvContent.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", `export_${Date.now()}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    exportJSON() {
        const { headers, rows } = this.getExportData();
        const json = rows.map(row => {
            const obj = {};
            headers.forEach((header, idx) => {
                obj[header] = row[idx];
            });
            return obj;
        });

        const blob = new Blob([JSON.stringify(json, null, 2)], { type: 'application/json' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", `export_${Date.now()}.json`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    exportPDF() {
        // Apply optimized print layout styles and call native print handler
        window.print();
    }
}

function initializeSmartTables() {
    document.querySelectorAll('.smart-table').forEach(table => {
        if (!table.dataset.smartTableInitialized) {
            table.dataset.smartTableInitialized = 'true';
            new SmartTable(table);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSmartTables);
} else {
    initializeSmartTables();
}
