define(["Magento_PageBuilder/js/mass-converter/widget-directive-abstract"], function (WidgetDirectiveAbstract) {
    var AmReviewDirective = function () {
        WidgetDirectiveAbstract.apply(this, arguments);
    };

    AmReviewDirective.prototype = Object.create(WidgetDirectiveAbstract.prototype);
    AmReviewDirective.prototype.constructor = AmReviewDirective;

    AmReviewDirective.prototype.fromDom = function (data, config) {
        var attributes = WidgetDirectiveAbstract.prototype.fromDom.call(this, data, config);
        data.title = attributes.title;
        data.reviews_count = attributes.reviews_count;
        data.higher_than = attributes.higher_than;
        data.review_type = attributes.review_type;
        data.template = attributes.template;
        data.current_category = attributes.current_category;
        data.enable_slider = attributes.enable_slider;

        return data;
    };

    AmReviewDirective.prototype.toDom = function (data, config) {
        data['type'] = "Amasty\\AdvancedReview\\Block\\Widget\\Reviews";
        if (data.template) {
            delete data.html;
            var attributes = Object.assign({}, data);
            delete(attributes.name);
            data[config.html_variable] = this.buildDirective(attributes);
        }

        return data;
    };

    return AmReviewDirective;
});
