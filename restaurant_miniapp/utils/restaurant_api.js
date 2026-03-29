const { request } = require('./request');

const restaurantApi = {
  tableInfo(token) {
    return request({ url: '/restaurant/table/info', method: 'GET', data: { token } });
  },
  menu(token) {
    return request({ url: '/restaurant/menu', method: 'GET', data: { token } });
  },
  cartGet(token) {
    return request({ url: '/restaurant/cart/get', method: 'GET', data: { token } });
  },
  cartAdd(data) {
    return request({ url: '/restaurant/cart/add', method: 'POST', data });
  },
  cartUpdate(data) {
    return request({ url: '/restaurant/cart/update', method: 'POST', data });
  },
  cartRemove(data) {
    return request({ url: '/restaurant/cart/remove', method: 'POST', data });
  },
  cartClear(data) {
    return request({ url: '/restaurant/cart/clear', method: 'POST', data });
  },
  orderCreate(data) {
    return request({ url: '/restaurant/order/create', method: 'POST', data });
  },
  orderList(token) {
    return request({ url: '/restaurant/order/list', method: 'GET', data: { token } });
  },
  orderDetail(token, id) {
    return request({ url: '/restaurant/order/detail', method: 'GET', data: { token, id } });
  },
  paymentGateways() {
    return request({ url: '/restaurant/payment/gateways', method: 'GET', data: {} });
  },
  paymentPay(data) {
    return request({ url: '/restaurant/payment/pay', method: 'POST', data });
  },
};

module.exports = { restaurantApi };

