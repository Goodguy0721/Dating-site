"use strict";
function storeCart(optionArr) {

	this.properties = {
		siteUrl: '/',
		langs: {add_cart_lang: '1', confirm_lang: '2', error_select: '3'},
		addProductButton: '.add_product',
		quickViewButton: '.quick_view_wrapper',
		selectOptions: '.select-options',
		preorderButton: '#preorder',
		hoverBlock: '.product_item',
		cartBlock: '#cart_block',
		loadCartBlock: '#load_cart_block',
		cartCount: '#cart_count',
		productCount: '#product_count',
		cartItem: '#cart_item_',
		cartItemCount: '#count_',
		cartItemStatus: '#status_',
		cartItemPrice: '#price_',
		cartTotal: '#cart_total',
		countUp: '#count_up',
		countDown: '#count_down',
		loadCart: '#load_cart',
		viewProduct: '#view_product',
		createPreorder: '#create_preorder',
		delItemCart: '#del_item_cart',
		actionBlock: '#action_block',
		option: '#option_',
		itemId: '#item_',
		cartItems: '#cart_items',
		goShoppingCart: '.go_shopping_cart',
		commonAncestor: 'body',
		addProductUrl: 'store/ajax_add_to_cart/',
		loadCartUrl: 'store/ajax_load_cart/',
		removeUrl: 'store/ajax_remove_from_cart/',
		quickView: 'store/ajax_quick_view/',
		createPreopderUrl: 'store/preorder/',
		animate: true,
		contentObj: new loadingContent({
			loadBlockWidth: '700px',
			loadBlockLeftType: 'center',
			loadBlockTopType: 'center',
			loadBlockTopPoint: 100,
			closeBtnClass: 'w'
		})
	};

	var _self = this;
	var _loadBlock = true;

	this.Init = function(options) {
		_self.properties = $.extend(_self.properties, options);
		_self.init_controls();
	};

	this.uninit = function() {
		$(_self.properties.commonAncestor)
			.off('click', _self.properties.loadCartBlock)
			.off('click', _self.properties.addProductButton)
			.off('click', _self.properties.quickViewButton)
			.off('mouseenter', _self.properties.hoverBlock)
			.off('mouseleave', _self.properties.hoverBlock)
			.off('click', _self.properties.countUp)
			.off('click', _self.properties.countDown)
			.off('click', _self.properties.delItemCart)
			.off('click', _self.properties.createPreorder)
			.off('click', _self.properties.preorderButton)
			.off('click', _self.properties.goShoppingCart)
			.off('click', _self.properties.selectOptions + ' li');
		return this;
	};

	this.init_controls = function() {		
		$(_self.properties.commonAncestor).off('click', _self.properties.loadCartBlock).on('click', _self.properties.loadCartBlock, function(){
			_self.loadCart();
		}).off('click', _self.properties.addProductButton).on('click', _self.properties.addProductButton, function(){
			_self.addToCart($(this));
		}).off('click', _self.properties.quickViewButton).on('click', _self.properties.quickViewButton, function(){
			var id_product = $(this).data('idproduct');
			_self.ajaxQuickView(id_product);
		}).off('mouseenter', _self.properties.hoverBlock).on('mouseenter', _self.properties.hoverBlock, function(){
			$(this).addClass('product_item_hover');
		}).off('mouseleave', _self.properties.hoverBlock).on('mouseleave', _self.properties.hoverBlock, function(){
			$(this).removeClass('product_item_hover');
		}).off('click', _self.properties.countUp).on('click', _self.properties.countUp, function(){
			_self.changeCount($(this).data('cartitem'), 1);
		}).off('click', _self.properties.countDown).on('click', _self.properties.countDown, function(){
			_self.changeCount($(this).data('cartitem'), -1);
		}).off('click', _self.properties.delItemCart).on('click', _self.properties.delItemCart, function(){
			_self.removeItems();
		}).off('click', _self.properties.createPreorder).on('click', _self.properties.createPreorder, function(){
			_self.preorder();
		}).off('click', _self.properties.preorderButton).on('click', _self.properties.preorderButton, function(){
			_self.savePreorder();
		}).off('click', _self.properties.goShoppingCart).on('click', _self.properties.goShoppingCart, function(){
			_self.shoppingCart();
		}).off('click', _self.properties.selectOptions + ' li').on('click', _self.properties.selectOptions + ' li', function(event){
			_self.checkOption($(this), event);
		});
	};
	
	
	this.addToCart = function (obj) {
		var id_product = obj.data('idproduct');
		var id_recipient = obj.data('idrecipient');
		var quick_view = obj.data('quick_view');
		if (quick_view) {
            _self.ajaxQuickView(id_product); 
            return;
        }
		var post_data = $(_self.properties.viewProduct).serialize();
		_request(_self.properties.addProductUrl, id_product + "/" + id_recipient, post_data, 'json', function(data) {
			var result = data;
			if (_self.properties.animate) {
				var source = obj.closest('#product_' + id_product).find('img');
				var i = $(_self.properties.productCount).index(obj);
				source.effect( "transfer", { to: $(_self.properties.productCount).eq( i ) }, 1000 );
				$(".ui-effects-transfer:last").css({"background-image": "url(" + source.attr("src") + ")","z-index":1001, "background-size":"100%"});
			}
			$(_self.properties.productCount).html(result['products_count']);
			obj.val(_self.properties.langs.add_cart_lang);			
		});
	};
	
	this.loadCart = function () {
		var count_prod = parseInt($(_self.properties.productCount).html());
		if(count_prod == 0) return;
		if(_loadBlock){
			_request(_self.properties.loadCartUrl, '', '', 'html', function(data) {
				_loadBlock = false;
				$(_self.properties.loadCart).html(data).show();
				var height = $(_self.properties.loadCart).height();
				if(height > 250){
					$('.cart-scroller').slimScroll({railVisible: true, size:'5px', position:'right'});
				}
			});
		}else{
			_loadBlock = true;
			$(_self.properties.loadCart).toggle();
		}
	};
	
	this.ajaxQuickView = function (id_product) {
		_request(_self.properties.quickView, id_product, '', 'json', function(data) {
			var result = data;
			_self.properties.contentObj.show_load_block(result['html']);
		});
	};
	
	this.changeCount = function (id, change) {
		$(_self.properties.cartItemCount+id).val(function(index, x){
			var count = 1;
			var total_sum = _self.getTotalSum();
			var sum = _self.getItemSum(id);
			if (change > 0) {
				count = parseInt(x)+1;
				$(_self.properties.itemId + id).text(count);
				total_sum = total_sum + sum;
				_self.setTotalSum(total_sum);
				_self.changeAllCount(change);
				return count;
			} else {
				if(x <= 1)return 1;
				count = parseInt(x)-1;
				$(_self.properties.itemId + id).text(count);
				total_sum = total_sum - sum;
				_self.setTotalSum(total_sum);
				_self.changeAllCount(change);
				return count;
			}
		});
	};
	
	this.changeAllCount = function (change) {
		$(_self.properties.cartCount).html(function(index, x){
			return parseInt(x)+change; 
		});
	};
	
	this.removeItems = function () {
		var items = _self.getItems();
		var totalSum = 0;
		var totalCount = 0;		
		if(items.length > 0){
			_request(_self.properties.removeUrl, '', items, 'json', function(data) {
				var result = data;
				if(result.products_count < 1){
					$(_self.properties.actionBlock).hide();
				}
				$(_self.properties.cartBlock+' input[type=checkbox]').each(function(){
					var item = $(this).data('cartitem');
					if(!$(this).prop('checked')){
						var count = _self.getItemCount(item);
						totalCount += count;
						var sum = count * _self.getItemSum(item);
						totalSum += sum ? sum : 0;
					}else{
						$(_self.properties.cartItem + item).hide("drop", {direction: "right"}, 500, function(){ $(this).remove();});
					} 
				});
				_self.setTotalSum(totalSum);
				$(_self.properties.cartCount).html(totalCount);
				if(totalCount == 0){
					$(_self.properties.cartItems).next().addClass('table-div').show();
				}
			});
		}
	};
	
	this.preorder = function () {
		var totalSum = 0;
		var totalCount = 0;
		var check = _self.getItems();
		if(!check){
			error_object.show_error_block(_self.properties.langs.error_select, 'error');
			return;
		}
		$(_self.properties.cartBlock+' input[type=checkbox]').each(function(){
			if($(this).prop('checked')){
				var count = _self.getItemCount($(this).data('cartitem'));
				totalCount += count;
				var sum = count * _self.getItemSum($(this).data('cartitem'));
				totalSum += sum ? sum : 0;
			} else {
				$(_self.properties.cartItem+$(this).data('cartitem')).hide("drop", {direction: "right"}, 500, function(){ $(this).remove();});
			}
		});
		_self.setTotalSum(totalSum);
		$(_self.properties.cartCount).html(totalCount);
		//$(_self.properties.createPreorder).val(_self.properties.langs.confirm_lang).attr('id', 'preorder');
		//$(_self.properties.delItemCart).remove();
		_self.savePreorder();
	};
	
	this.savePreorder = function () {
		if($('input:checked').length < 1) return;
		$(_self.properties.cartBlock+' input[type=checkbox]').each(function(){
			if(!$(this).prop('checked')){$(_self.properties.cartItem+$(this).data('cartitem')).hide("drop", {direction: "right"}, 500, function(){ $(this).remove();});}
		});
		$(_self.properties.cartBlock).submit();
	};
	
	this.getItems = function () {
		var formData = '';
		$('input:checked').each(function(){
			formData += '&'+$("#cart_item_"+$(this).data('cartitem')).find('input').serialize();
		});
		return formData;
	};
	
	this.getItemCount = function (id) {
		return parseInt($(_self.properties.cartItemCount+id).val());
	};
	
	this.getItemSum = function (id) {
		return parseInt($(_self.properties.cartItemPrice+id).html());
	};
	
	this.getTotalSum = function () {
		var total = parseInt($(_self.properties.cartTotal).html());
		if(total < 0)
			total = _self.recalculateTotalSum();
		return total;
	};
	
	this.recalculateTotalSum = function () {
		var totalSum = 0;
		$('input:checked').each(function(){
			var count = _self.getItemCount($(this).data('cartitem'));
			var sum = count * _self.getItemSum($(this).data('cartitem'));
			totalSum += sum ? sum : 0;			
		});
		return totalSum;
	};
	
	this.setTotalSum = function (sum) {
		$(_self.properties.cartTotal).html(sum);
	};
	
	this.shoppingCart = function () {
		locationHref(_self.properties.siteUrl + 'store/cart/');
	};
	
	this.checkOption = function (obj) {
		obj.parents('ul').find('li').removeClass('selected-block');
		obj.parents('ul').find('input').prop('disabled', true);
		obj.addClass('selected-block');
		var value = obj.data('value');
		var option = obj.data('option');
		$(_self.properties.option + option + '_' + value).prop('disabled', false);
	};
	
	var _request = function(url, param, data, dataType, successCb) {
		$.ajax({
			url: _self.properties.siteUrl + url + param,
			type: 'POST',
            cache: false,
			data: data,
            dataType: dataType,
			success: function(data) {
				if (data.errors) {
					error_object.show_error_block(data.errors, 'error');
				} else {
					successCb(data);
				}
			}
		});
	};
	

	_self.Init(optionArr);
}
