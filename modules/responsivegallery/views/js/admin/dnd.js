/*
 *  @license
 */

/*
 * Modification du fichier dnd de base pour avoir les positions des éléments allant de 1 à n.
 *
 */
$(document).ready(function() {
	initTableDnD();
});

function objToString(obj) {
    var str = '';
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            str += p + '=' + obj[p] + '&';
        }
    }
    return str;
}

function initTableDnD(table)
{
	if (typeof(table) == 'undefined')
		table = 'table.tableDnD';

	$(table).tableDnD({
		onDragStart: function(table, row) {
			originalOrder = $.tableDnD.serialize();
			reOrder = ':even';
			if (table.tBodies[0].rows[1] && $('#' + table.tBodies[0].rows[1].id).hasClass('alt_row'))
				reOrder = ':odd';
			$(table).find('#' + row.id).parent('tr').addClass('myDragClass');
		},
		dragHandle: 'dragHandle',
		onDragClass: 'myDragClass',
		onDrop: function(table, row) {
			if (originalOrder != $.tableDnD.serialize()) {
				var way = (originalOrder.indexOf(row.id) < $.tableDnD.serialize().indexOf(row.id))? 1 : 0;
				var ids = row.id.split('_');
				var tableDrag = table;
				var params = '';
				table.id = table.id.replace('table-', '');
				if (table.id == 'cms_block_0' || table.id == 'cms_block_1')
					params = {
						updatePositions: true,
						configure: 'blockcms'
					};
				else if (table.id == 'category')
				{
					params = {
						action: 'updatePositions',
						id_category_parent: ids[1],
						id_category_to_move: ids[2],
						way: way
					};
				}
				else if (table.id == 'cms_category')
					params = {
						action: 'updateCmsCategoriesPositions',
						id_cms_category_parent: ids[1],
						id_cms_category_to_move: ids[2],
						way: way
					};
				else if (table.id == 'cms')
					params = {
						action: 'updateCmsPositions',
						id_cms_category: ids[1],
						id_cms: ids[2],
						way: way
					};
				else if (come_from == 'AdminModulesPositions')
					params = {
						action: 'updatePositions',
						id_hook: ids[0],
						id_module: ids[1],
						way: way
					};
				else if (table.id.indexOf('attribute') != -1 && table.id != 'attribute_group') {
					params = {
						action: 'updateAttributesPositions',
						id_attribute_group: ids[1],
						id_attribute: ids[2],
						way: way
					};
				}
				else if (table.id == 'attribute_group') {
					params = {
						action: 'updateGroupsPositions',
						id_attribute_group: ids[2],
						way: way
					}
				}
				else if (table.id == 'product') {
					params = {
						action: 'updatePositions',
						id_category: ids[1],
						id_product: ids[2],
						way: way
					};
				}
				// default
				else
				{
					params = {
						action : 'updatePositions',
						id : ids[2],
						way: way
					}
				}
				params['ajax'] = 1;
				$.ajax({
					type: 'POST',
					headers: { "cache-control": "no-cache" },
					async: false,
					url: currentIndex + '&token=' + token + '&' + 'rand=' + new Date().getTime(),
					data: $.tableDnD.serialize() + '&' + objToString(params) ,
					success: function(data) {
                        var nodrag_lines = $(tableDrag).find('tr:not(".nodrag")');
						if (come_from == 'AdminModulesPositions')
						{
							nodrag_lines.each(function(i) {
								$(this).find('.positions').html(i+1);
							});
						}
						else
						{
							if (table.id == 'product' || table.id.indexOf('attribute') != -1 || table.id == 'attribute_group' || table.id == 'feature')
							{
								var reg = /_[0-9][0-9]*$/g;
							}
							else
							{
								var reg = /_[0-9]$/g;
							}

							var up_reg  = new RegExp('position=[-]?[0-9]+&');
							nodrag_lines.each(function(i) {
								$(this).attr('id', $(this).attr('id').replace(reg, '_' + i));
								$(this).find('.positions').text(i+1);
							});
						}

						nodrag_lines.removeClass('odd');
						nodrag_lines.filter(':odd').addClass('odd');
						nodrag_lines.children('td.dragHandle').find('a').attr('disabled',false);

						if (typeof alternate !== 'undefined' && alternate) {
							nodrag_lines.children('td.dragHandle:first').find('a:odd').attr('disabled',true);
							nodrag_lines.children('td.dragHandle:last').find('a:even').attr('disabled',true);
						}
						else {
							nodrag_lines.children('td.dragHandle:first').find('a:even').attr('disabled',true);
							nodrag_lines.children('td.dragHandle:last').find('a:odd').attr('disabled',true);
						}
						showSuccessMessage(update_success_msg);
					}
				});
			}
		}
	});
}
