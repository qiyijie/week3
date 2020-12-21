// pages/home/home.js
Page({

     /**
      * 页面的初始数据
      */
     data: {
          data:null,
          ap:'',
          show:'show'
     },

     /**
      * 生命周期函数--监听页面加载
      */
     onLoad: function (options) {
          this.request();

     },
     request(){
          let that = this;
          wx.request({
               url: 'http://www.rikao.com/index.php/Article/Index/show', //仅为示例，并非真实的接口地址
               data: {},
               header: {
                 'content-type': 'application/json' // 默认值
               },
               success (res) {
                that.setData({data:res.data});
               }
             })
     },
     click(e){
          let that = this;
          // console.log(e.target.dataset.index);
          wx.request({
               url: 'http://www.rikao.com/index.php/Article/Index/update/id/'+e.target.dataset.index, //仅为示例，并非真实的接口地址
               data: {
                 x: '',
                 y: ''
               },
               header: {
                 'content-type': 'application/json' // 默认值
               },
               success (res) {
               //   console.log(res.data)
                 let data = that.data.data;
                 console.log(data);
                 data.map((item,key)=>{
                      if(e.target.dataset.index == item.id){
                           item.show = 1
                          return item.collect = res.data
                      }
                 });
                 wx.showToast({
                    title: '收藏成功',
                    icon: 'success',
                    duration: 2000
                  })

                 that.setData({data});
               }
             })
     }
})