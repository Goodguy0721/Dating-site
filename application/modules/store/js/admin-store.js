function AdminStore(optionArr) {
    this.properties = {
        siteUrl: '',
        inputPrice: 'input[name^="price"]'
    };
    var _self = this;
    this.Init = function (options) {};

    this.formatPrice = function (obj) {
        if (obj.value.match(/[^0-9]/g)) {
            obj.value = obj.value.replace(/[^0-9.]/g, '');
            obj.value = Math.ceil(obj.value);
        }
    };
    _self.Init(optionArr);
}