## DPlayer-Typecho
[DPlayer](https://github.com/DIYgod/DPlayer) for typecho

### 使用方式
下载后将文件夹名改为DPlayer上传启用即可

默认不自动播放，弹幕开启
```
[dplayer url="http://xxx.com/xxx.mp4" pic="http://xxx.com/xxx.jpg"/]
```

关闭弹幕
```
[dplayer url="http://xxx.com/xxx.mp4" pic="http://xxx.com/xxx.jpg" danmu="false"/]
```

开启自动播放
```
[dplayer url="http://xxx.com/xxx.mp4" pic="http://xxx.com/xxx.jpg" autoplay="true"/]
```

添加额外弹幕源(例：bilibili弹幕)
```
[dplayer url="http://xxx.com/xxx.mp4" pic="http://xxx.com/xxx.jpg" autoplay="true" addition="https://api.prprpr.me/dplayer/bilibili?aid=7286894"/]
```

### 设置截图
![](https://raw.githubusercontent.com/volio/DPlayer-for-typecho/master/assets/screenshot.png)

### LICENSE
MIT © [Volio](https://niconiconi.org)