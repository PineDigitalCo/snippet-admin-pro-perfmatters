(function () {
	'use strict';

	var config = window.SAPFP_PMCS;
	if (!config || !Array.isArray(config.locationCatalog)) {
		return;
	}

	injectLocationColumn(config);
	injectBulkLocationBar(config);
	injectBulkDuplicateButton(config);
	injectDuplicateRowActions(config);

	function injectLocationColumn(cfg) {
		var table = document.querySelector('table.wp-list-table.pmcs-snippets');
		if (!table || table.dataset.sapfpLocationColumn === '1') {
			return;
		}

		var headerRow = table.querySelector('thead tr');
		if (!headerRow) {
			return;
		}

		var typeHeader = findColumnHeader(headerRow, 'Type');
		if (!typeHeader) {
			return;
		}

		var locationHeader = document.createElement('th');
		locationHeader.className = 'sapfp-location-column manage-column column-location sortable';
		locationHeader.scope = 'col';
		locationHeader.setAttribute('aria-sort', 'none');

		var sortLink = document.createElement('a');
		sortLink.href = '#';
		sortLink.className = 'sapfp-location-sort';

		var labelSpan = document.createElement('span');
		labelSpan.textContent = cfg.strings.locationColumn || 'Location';
		sortLink.appendChild(labelSpan);

		var indicators = document.createElement('span');
		indicators.className = 'sorting-indicators';
		indicators.innerHTML =
			'<span class="sorting-indicator asc" aria-hidden="true"></span>' +
			'<span class="sorting-indicator desc" aria-hidden="true"></span>';
		sortLink.appendChild(indicators);

		locationHeader.appendChild(sortLink);
		typeHeader.insertAdjacentElement('afterend', locationHeader);

		var rows = table.querySelectorAll('tbody tr');
		rows.forEach(function (row) {
			if (row.querySelector('.sapfp-location-cell')) {
				return;
			}

			var checkbox = row.querySelector('input[name="snippets[]"]');
			if (!checkbox) {
				return;
			}

			var typeCell = row.querySelector('.pmcs-snippet-type-badge');
			if (!typeCell || !typeCell.closest('td')) {
				return;
			}

			var locationCell = document.createElement('td');
			locationCell.className = 'sapfp-location-cell';
			locationCell.textContent = getLocationLabel(cfg, checkbox.value);
			typeCell.closest('td').insertAdjacentElement('afterend', locationCell);
		});

		var initialSort = getLocationSortFromUrl();
		if (initialSort) {
			sortTableByLocation(table, locationHeader, initialSort);
		}

		sortLink.addEventListener('click', function (event) {
			event.preventDefault();

			var nextSort = 'asc';
			if (locationHeader.classList.contains('sorted')) {
				nextSort = locationHeader.classList.contains('asc') ? 'desc' : 'asc';
			}

			sortTableByLocation(table, locationHeader, nextSort);
			setLocationSortInUrl(nextSort);
			syncRedirectLocationSort(nextSort);
		});

		table.dataset.sapfpLocationColumn = '1';
	}

	function sortTableByLocation(table, header, direction) {
		var tbody = table.querySelector('tbody');
		if (!tbody) {
			return;
		}

		var rows = Array.prototype.filter.call(tbody.querySelectorAll('tr'), function (row) {
			return row.querySelector('.sapfp-location-cell');
		});

		rows.sort(function (rowA, rowB) {
			return compareLocationRows(rowA, rowB, direction);
		});

		rows.forEach(function (row) {
			tbody.appendChild(row);
		});

		setSortHeaderState(header, direction);
	}

	function compareLocationRows(rowA, rowB, direction) {
		var a = getLocationSortValue(rowA);
		var b = getLocationSortValue(rowB);

		if (a.empty && !b.empty) {
			return 1;
		}
		if (!a.empty && b.empty) {
			return -1;
		}

		var cmp = a.label.localeCompare(b.label, undefined, { sensitivity: 'base' });
		return direction === 'desc' ? -cmp : cmp;
	}

	function getLocationSortValue(row) {
		var cell = row.querySelector('.sapfp-location-cell');
		var text = cell ? cell.textContent.trim() : '';

		return {
			label: text === '--' ? '' : text,
			empty: text === '' || text === '--',
		};
	}

	function setSortHeaderState(header, direction) {
		header.classList.add('sorted');
		header.classList.remove('asc', 'desc');
		header.classList.add(direction);
		header.setAttribute('aria-sort', direction === 'asc' ? 'ascending' : 'descending');
	}

	function getLocationSortFromUrl() {
		var params = new URLSearchParams(window.location.search);
		var sort = params.get('sapfp_location_sort');

		return sort === 'asc' || sort === 'desc' ? sort : '';
	}

	function setLocationSortInUrl(direction) {
		var url = new URL(window.location.href);
		url.searchParams.set('sapfp_location_sort', direction);
		window.history.replaceState({}, '', url.toString());
	}

	function syncRedirectLocationSort(direction) {
		var input = document.getElementById('sapfp-redirect-location-sort');
		if (input) {
			input.value = direction;
		}
	}

	function getLocationLabel(cfg, fileName) {
		if (!fileName || !cfg.snippetLocations || typeof cfg.snippetLocations !== 'object') {
			return '--';
		}

		return cfg.snippetLocations[fileName] || '--';
	}

	function findColumnHeader(headerRow, label) {
		var headers = headerRow.querySelectorAll('th');
		for (var i = 0; i < headers.length; i++) {
			if (headers[i].textContent.trim() === label) {
				return headers[i];
			}
		}

		return null;
	}

	function injectBulkLocationBar(cfg) {
		var bulkSelect = document.getElementById('bulk-action-selector-top');
		if (!bulkSelect || document.getElementById('sapfp-apply-location')) {
			return;
		}

		var tablenav = bulkSelect.closest('.tablenav.top');
		if (!tablenav) {
			return;
		}

		var bar = document.createElement('div');
		bar.className = 'alignleft actions sapfp-bulk-location-bar';

		var label = document.createElement('label');
		label.setAttribute('for', 'sapfp-new-location');
		label.textContent = cfg.strings.changeLocationTo;

		var select = document.createElement('select');
		select.id = 'sapfp-new-location';
		select.disabled = true;

		var button = document.createElement('button');
		button.type = 'button';
		button.className = 'button';
		button.id = 'sapfp-apply-location';
		button.textContent = cfg.strings.apply;
		button.disabled = true;

		bar.appendChild(label);
		bar.appendChild(select);
		bar.appendChild(button);

		var bulkActions = bulkSelect.closest('.alignleft.actions');
		if (bulkActions && bulkActions.parentNode) {
			bulkActions.parentNode.insertBefore(bar, bulkActions.nextSibling);
		} else {
			tablenav.insertBefore(bar, tablenav.firstChild);
		}

		var table = document.querySelector('table.wp-list-table.pmcs-snippets');

		function refreshLocationOptions() {
			var types = getSelectedSnippetTypes();
			populateLocationSelect(cfg, select, types);
			var hasOptions = select.options.length > 1 && !select.disabled;
			button.disabled = !hasOptions;
		}

		if (table) {
			table.addEventListener('change', function (event) {
				var target = event.target;
				if (
					target.matches('input[name="snippets[]"]') ||
					target.id === 'cb-select-all-1' ||
					target.id === 'cb-select-all-2'
				) {
					refreshLocationOptions();
				}
			});
		}

		refreshLocationOptions();

		button.addEventListener('click', function () {
			var checked = document.querySelectorAll('input[name="snippets[]"]:checked');
			if (!checked.length) {
				window.alert(cfg.strings.selectSnippets);
				return;
			}

			if (select.value === '__none__') {
				window.alert(cfg.strings.chooseLocation);
				return;
			}

			var form = document.getElementById('sapfp-bulk-location-form');
			var inputsContainer = document.getElementById('sapfp-snippet-inputs');
			if (!form || !inputsContainer) {
				return;
			}

			inputsContainer.innerHTML = '';

			checked.forEach(function (checkbox) {
				var input = document.createElement('input');
				input.type = 'hidden';
				input.name = 'snippets[]';
				input.value = checkbox.value;
				inputsContainer.appendChild(input);
			});

			document.getElementById('sapfp-new-location-input').value = select.value;
			form.submit();
		});
	}

	function getSelectedSnippetTypes() {
		var types = [];
		var seen = {};

		document.querySelectorAll('input[name="snippets[]"]:checked').forEach(function (checkbox) {
			var row = checkbox.closest('tr');
			if (!row) {
				return;
			}

			var badge = row.querySelector('.pmcs-snippet-type-badge');
			if (!badge) {
				return;
			}

			var type = (badge.getAttribute('data-snippet-type') || badge.textContent || '').trim().toLowerCase();
			if (type && !seen[type]) {
				seen[type] = true;
				types.push(type);
			}
		});

		return types;
	}

	function populateLocationSelect(cfg, select, types) {
		while (select.firstChild) {
			select.removeChild(select.firstChild);
		}

		if (!types.length) {
			var waitOption = document.createElement('option');
			waitOption.value = '__none__';
			waitOption.textContent = cfg.strings.selectSnippetsFirst || 'Select Snippets to See Locations';
			select.appendChild(waitOption);
			select.disabled = true;
			return;
		}

		var options = getCatalogOptionsForTypes(cfg.locationCatalog, types);

		if (!options.length) {
			var emptyOption = document.createElement('option');
			emptyOption.value = '__none__';
			emptyOption.textContent = cfg.strings.noCommonLocations || 'No Common Locations for Selected Types';
			select.appendChild(emptyOption);
			select.disabled = true;
			return;
		}

		var placeholder = document.createElement('option');
		placeholder.value = '__none__';
		placeholder.textContent = cfg.strings.selectLocation || 'Select Location…';
		select.appendChild(placeholder);

		options.forEach(function (location) {
			var option = document.createElement('option');
			option.value = location.value === '' ? '__everywhere__' : location.value;
			option.textContent = location.label;
			select.appendChild(option);
		});

		select.disabled = false;
	}

	function getCatalogOptionsForTypes(catalog, types) {
		return catalog.filter(function (entry) {
			return types.every(function (type) {
				return entry.codeTypes.indexOf(type) !== -1;
			});
		});
	}

	function injectBulkDuplicateButton(cfg) {
		if (document.getElementById('sapfp-duplicate-selected')) {
			return;
		}

		var bulkSelect = document.getElementById('bulk-action-selector-top');
		var tablenav = bulkSelect ? bulkSelect.closest('.tablenav.top') : null;
		if (!tablenav) {
			return;
		}

		var button = document.createElement('button');
		button.type = 'button';
		button.className = 'button sapfp-bulk-duplicate-button';
		button.id = 'sapfp-duplicate-selected';
		button.textContent = cfg.strings.duplicateSelected || 'Duplicate Selected';
		button.disabled = true;

		var locationBar = document.querySelector('.sapfp-bulk-location-bar');
		if (locationBar && locationBar.parentNode) {
			locationBar.parentNode.insertBefore(button, locationBar.nextSibling);
		} else {
			var bulkActions = bulkSelect.closest('.alignleft.actions');
			if (bulkActions && bulkActions.parentNode) {
				bulkActions.parentNode.insertBefore(button, bulkActions.nextSibling);
			} else {
				tablenav.insertBefore(button, tablenav.firstChild);
			}
		}

		var table = document.querySelector('table.wp-list-table.pmcs-snippets');

		function refreshDuplicateButton() {
			var checked = document.querySelectorAll('input[name="snippets[]"]:checked');
			button.disabled = checked.length === 0;
		}

		if (table) {
			table.addEventListener('change', function (event) {
				var target = event.target;
				if (
					target.matches('input[name="snippets[]"]') ||
					target.id === 'cb-select-all-1' ||
					target.id === 'cb-select-all-2'
				) {
					refreshDuplicateButton();
				}
			});
		}

		refreshDuplicateButton();

		button.addEventListener('click', function () {
			var checked = document.querySelectorAll('input[name="snippets[]"]:checked');
			if (!checked.length) {
				window.alert(cfg.strings.selectSnippets);
				return;
			}

			submitDuplicateForm(
				Array.prototype.map.call(checked, function (checkbox) {
					return checkbox.value;
				})
			);
		});
	}

	function injectDuplicateRowActions(cfg) {
		var table = document.querySelector('table.wp-list-table.pmcs-snippets');
		if (!table || table.dataset.sapfpDuplicateActions === '1') {
			return;
		}

		table.querySelectorAll('tbody tr').forEach(function (row) {
			var checkbox = row.querySelector('input[name="snippets[]"]');
			var rowActions = row.querySelector('.row-actions');
			var exportAction = rowActions ? rowActions.querySelector('.export') : null;

			if (!checkbox || !rowActions || !exportAction || rowActions.querySelector('.sapfp-duplicate-action')) {
				return;
			}

			var duplicateAction = document.createElement('span');
			duplicateAction.className = 'duplicate sapfp-duplicate-action';
			duplicateAction.innerHTML =
				' | <a href="#" class="sapfp-duplicate-one">' +
				(cfg.strings.duplicate || 'Duplicate') +
				'</a> ';

			exportAction.insertAdjacentElement('afterend', duplicateAction);

			var link = duplicateAction.querySelector('.sapfp-duplicate-one');
			if (!link) {
				return;
			}

			link.addEventListener('click', function (event) {
				event.preventDefault();
				submitDuplicateForm([checkbox.value]);
			});
		});

		table.dataset.sapfpDuplicateActions = '1';
	}

	function submitDuplicateForm(fileNames) {
		var form = document.getElementById('sapfp-bulk-duplicate-form');
		var inputsContainer = document.getElementById('sapfp-duplicate-inputs');

		if (!form || !inputsContainer || !fileNames.length) {
			return;
		}

		inputsContainer.innerHTML = '';

		fileNames.forEach(function (fileName) {
			var input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'snippets[]';
			input.value = fileName;
			inputsContainer.appendChild(input);
		});

		form.submit();
	}
})();
