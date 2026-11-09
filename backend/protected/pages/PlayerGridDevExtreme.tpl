<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players - DevExtreme</title>
    <com:THead />
    <link rel="stylesheet" href="https://cdn3.devexpress.com/jslib/24.2.7/css/dx.light.css">
    <script src="https://cdn3.devexpress.com/jslib/24.2.7/js/dx.all.js"></script>
</head>
<body>
    <com:TForm>
        <header><h1>Players</h1></header>
        <p>Players with the last name Williams or Willams cannot be selected.</p>
        <div id="player-grid"></div>
        <p>Selected player IDs:</p>
        <pre id="selected-player-ids">[]</pre>
        <script type="application/json" id="player-grid-data"><%= $this->getRowsJson() %></script>
        <script>
            (function () {
                var rows = JSON.parse(document.getElementById('player-grid-data').textContent);
                var selectionDisplay = document.getElementById('selected-player-ids');
                var selectAllCheckBox;
                var updatingSelectAll = false;
                var changingSelectAll = false;

                function isSelectable(row) {
                    var lastName = String(row.last_name || '').trim().toLowerCase();
                    return lastName !== 'williams' && lastName !== 'willams';
                }

                var excludedRows = rows.filter(function (row) {
                    return !isSelectable(row);
                });

                function selectableRowsForFilter(grid) {
                    var filter = grid.getCombinedFilter(true);
                    var matchingRows = filter
                        ? DevExpress.data.query(rows).filter(filter).toArray()
                        : rows;
                    return matchingRows.filter(isSelectable);
                }

                function updateSelectAll(grid) {
                    if (!selectAllCheckBox) {
                        return;
                    }
                    var selectableRows = selectableRowsForFilter(grid);
                    var selectedCount = selectableRows.filter(function (row) {
                        return grid.isRowSelected(row.id);
                    }).length;
                    updatingSelectAll = true;
                    try {
                        selectAllCheckBox.option({
                            disabled: selectableRows.length === 0,
                            value: selectedCount === 0 ? false
                                : selectedCount === selectableRows.length ? true : undefined
                        });
                    } finally {
                        updatingSelectAll = false;
                    }
                }

                new DevExpress.ui.dxDataGrid(document.getElementById('player-grid'), {
                    dataSource: rows,
                    keyExpr: 'id',
                    showBorders: true,
                    selection: {
                        mode: 'multiple',
                        allowSelectAll: true,
                        selectAllMode: 'allPages',
                        showCheckBoxesMode: 'always',
                        deferred: false
                    },
                    selectedRowKeys: [],
                    filterRow: { visible: true },
                    paging: { pageSize: 10 },
                    pager: {
                        visible: true,
                        showPageSizeSelector: true,
                        allowedPageSizes: [10, 25, 50]
                    },
                    columns: [
                        {
                            dataField: 'team_name',
                            caption: 'Team name',
                            sortOrder: 'asc',
                            cellTemplate: function (cell, options) {
                                if (!options.component.isRowSelected(options.key)) {
                                    cell.textContent = options.value;
                                }
                            }
                        },
                        { dataField: 'first_name', caption: 'First name' },
                        { dataField: 'last_name', caption: 'Last name' },
                        { dataField: 'position', caption: 'Position' },
                        { dataField: 'jersey_number', caption: 'Jersey number', dataType: 'number' },
                        { dataField: 'points', caption: 'Game stats (Points)', dataType: 'number' }
                    ],
                    onSelectionChanged: function (e) {
                        var excludedKeys = excludedRows.filter(function (row) {
                            return e.component.isRowSelected(row.id);
                        }).map(function (row) {
                            return row.id;
                        });
                        if (excludedKeys.length) {
                            e.component.deselectRows(excludedKeys).catch(function (error) {
                                console.error('Unable to exclude unselectable players:', error);
                            });
                            return;
                        }
                        console.log('Selection changed:', e);
                        selectionDisplay.textContent = JSON.stringify(e.selectedRowKeys, null, 2);
                        e.component.repaint();
                    },
                    onContentReady: function (e) {
                        var grid = e.component;
                        var visibleRows = grid.getVisibleRows();
                        visibleRows.forEach(function (row) {
                            if (row.rowType !== 'data') {
                                return;
                            }
                            var rowElement = grid.getRowElement(row.rowIndex);
                            if (rowElement && !rowElement.nodeType) {
                                rowElement = rowElement[0];
                            }
                            var checkboxElement = rowElement
                                && rowElement.querySelector('.dx-select-checkbox');
                            var checkbox = checkboxElement
                                && DevExpress.ui.dxCheckBox.getInstance(checkboxElement);
                            if (checkbox) {
                                checkbox.option('disabled', !isSelectable(row.data));
                            }
                        });

                        var headerCheckboxElement = document.querySelector(
                            '#player-grid .dx-header-row .dx-select-checkbox'
                        );
                        var headerCheckbox = headerCheckboxElement
                            && DevExpress.ui.dxCheckBox.getInstance(headerCheckboxElement);
                        if (headerCheckbox && headerCheckbox !== selectAllCheckBox) {
                            selectAllCheckBox = headerCheckbox;
                            headerCheckbox.option('onValueChanged', function (event) {
                                if (!event.event || updatingSelectAll || changingSelectAll) {
                                    return;
                                }
                                event.event.preventDefault();
                                event.event.stopPropagation();
                                var keys = selectableRowsForFilter(grid).map(function (row) {
                                    return row.id;
                                });
                                changingSelectAll = true;
                                var action;
                                try {
                                    action = event.value ? grid.selectRows(keys, true) : grid.deselectRows(keys);
                                } finally {
                                    changingSelectAll = false;
                                }
                                action.catch(function (error) {
                                    console.error('Unable to update player selection:', error);
                                });
                            });
                        }
                        updateSelectAll(e.component);
                    }
                });
            }());
        </script>
    </com:TForm>
</body>
</html>
