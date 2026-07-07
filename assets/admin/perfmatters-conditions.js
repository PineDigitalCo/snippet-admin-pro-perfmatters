jQuery(function ($) {
	var cfg = window.SAPFP_CONDITIONS || {};
	var strings = cfg.strings || {};
	var state = normalizeConfig(cfg.config || {});
	var sentinelRule = cfg.sentinel || 'sapfp:logic';

	var rows = [
		{
			key: 'include_logic',
			label: strings.includeLogic,
			orText: strings.includeOrShort,
			andText: strings.includeAndShort
		},
		{
			key: 'exclude_logic',
			label: strings.excludeLogic,
			orText: strings.excludeOrShort,
			andText: strings.excludeAndShort
		},
		{
			key: 'section_logic',
			label: strings.sectionLogic,
			hint: strings.sectionHint,
			orText: strings.sectionOrShort,
			andText: strings.sectionAndShort
		}
	];

	function normalizeConfig(config) {
		return {
			include_logic: config.include_logic === 'and' ? 'and' : 'or',
			exclude_logic: config.exclude_logic === 'and' ? 'and' : 'or',
			section_logic: config.section_logic === 'and' ? 'and' : 'or'
		};
	}

	function isDefaultConfig(config) {
		return config.include_logic === 'or'
			&& config.exclude_logic === 'or'
			&& config.section_logic === 'and';
	}

	function syncHiddenInput() {
		$('#sapfp-condition-logic-json').val(JSON.stringify(state));
	}

	function renderPanel() {
		if ($('#sapfp-condition-logic').length) {
			return;
		}

		var $anchor = $('#pmcs-conditions');
		if (!$anchor.length) {
			return;
		}

		var body = rows.map(renderRow).join('');

		var html = ''
			+ '<div id="sapfp-condition-logic">'
			+ '<div class="sapfp-logic-header">'
			+ '<h4>' + escapeHtml(strings.panelTitle || 'Condition logic') + '</h4>'
			+ '<p>' + escapeHtml(strings.panelDescription || '') + '</p>'
			+ '</div>'
			+ '<div class="sapfp-logic-grid" role="group" aria-label="' + escapeHtml(strings.panelTitle || 'Condition logic') + '">'
			+ '<div class="sapfp-logic-grid-head" aria-hidden="true">'
			+ '<span class="sapfp-logic-grid-label"></span>'
			+ '<span class="sapfp-logic-grid-col">' + escapeHtml(strings.orBadge || 'OR') + '</span>'
			+ '<span class="sapfp-logic-grid-col">' + escapeHtml(strings.andBadge || 'AND') + '</span>'
			+ '</div>'
			+ body
			+ '</div>'
			+ '<input type="hidden" name="sapfp_condition_logic" id="sapfp-condition-logic-json" value="" />'
			+ '</div>';

		$anchor.after(html);
		syncHiddenInput();
		bindPanelEvents();
		updateRowBadges();
	}

	function renderRow(row) {
		var hint = row.hint
			? '<span class="sapfp-logic-row-hint">' + escapeHtml(row.hint) + '</span>'
			: '';

		return '<div class="sapfp-logic-grid-row" data-logic-key="' + row.key + '">'
			+ '<div class="sapfp-logic-grid-label">'
			+ '<span class="sapfp-logic-row-title">' + escapeHtml(row.label || row.key) + '</span>'
			+ hint
			+ '</div>'
			+ tile(row.key, 'or', row.orText)
			+ tile(row.key, 'and', row.andText)
			+ '</div>';
	}

	function tile(key, value, text) {
		var id = 'sapfp-' + key + '-' + value;
		var checked = state[key] === value ? ' checked' : '';

		return '<label class="sapfp-logic-tile" for="' + id + '">'
			+ '<input type="radio" id="' + id + '" name="sapfp_' + key + '" value="' + value + '"' + checked + ' />'
			+ '<span class="sapfp-logic-tile-text">' + escapeHtml(text || value) + '</span>'
			+ '</label>';
	}

	function bindPanelEvents() {
		$('#sapfp-condition-logic').on('change', 'input[type="radio"]', function () {
			var key = $(this).attr('name').replace('sapfp_', '');
			state[key] = $(this).val();
			syncHiddenInput();
			updateRowBadges();
		});
	}

	function hideSentinelRows() {
		$('#pmcs-conditions-wrapper .condition').each(function () {
			var $row = $(this);
			var rule = $row.find('select.condition-select').val() || '';
			if (rule === sentinelRule) {
				$row.addClass('sapfp-sentinel-row');
			}
		});
	}

	function updateRowBadges() {
		$('.sapfp-condition-row-badge').remove();

		var includeBadge = state.include_logic === 'and'
			? (strings.andBadge || 'AND')
			: (strings.orBadge || 'OR');

		var $includeRows = $('#pmcs-conditions-wrapper .pmcs-condition-type').first()
			.find('.perfmatters-input-row-container .condition:visible');

		$includeRows.each(function (index) {
			if (index === 0) {
				return;
			}

			$('<div class="sapfp-condition-row-badge' + (state.include_logic === 'and' ? ' is-and' : '') + '"></div>')
				.text(includeBadge)
				.insertBefore($(this));
		});
	}

	function escapeHtml(text) {
		return String(text)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	renderPanel();
	hideSentinelRows();

	$(document).on('click', '.perfmatters-add-input-row', function () {
		window.setTimeout(function () {
			hideSentinelRows();
			updateRowBadges();
		}, 0);
	});

	$('#pmcs-conditions-wrapper').on('click', '.perfmatters-delete-input-row', function () {
		window.setTimeout(updateRowBadges, 0);
	});

	$('form[method="POST"]').on('submit', function () {
		syncHiddenInput();
		if (isDefaultConfig(state)) {
			$('#sapfp-condition-logic-json').prop('disabled', true);
		}
	});
});
