/**
 * Picasa Webalbum Integration jQuery plugin
 * This library was inspired aon pwa by Dieter Raber
 * @name jquery.pwi.js
 * @author Jeroen Diderik - http://www.jdee.nl/
 * @author Johan Borkhuis - http://www.borkhuis.com/
 * @revision 1.5.0
 * @date September 18, 2011
 * @copyright (c) 2010-2011 Jeroen Diderik(www.jdee.nl)
 * @license Creative Commons Attribution-Share Alike 3.0 Netherlands License - http://creativecommons.org/licenses/by-sa/3.0/nl/
 * @Visit http://pwi.googlecode.com/ for more informations, duscussions etc about this library
 */
function formatTitle(a,b,c,d){var e='<table id="fancybox-title-float-wrap" cellpadding="0" cellspacing="0"><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">'+a+'</td><td id="fancybox-title-float-right"></td></tr></table>';if(d.orig.context.parentNode.childElementCount>1){var f=d.orig.context.parentNode.getElementsByClassName("downloadlink");if(f.length>0){var g='<a style="color: #FFF;" href="'+f[0].href+'">Download</a>';e='<table id="fancybox-title-float-wrap" cellpadding="0" cellspacing="0"><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">'+a+"  "+g+'</td><td id="fancybox-title-float-right"></td></tr></table>'}}return e}(function(a){var b,c={};a.fn.pwi=function(c){function t(b,c){if(b){if(e.loadingImage.length>0){var f=document.getElementById(e.loadingImage);if(f){f.style.display="block"}}document.body.style.cursor="wait";if(a.blockUI){d.block(e.blockUIConfig)}}else{if(e.loadingImage.length>0){var f=document.getElementById(e.loadingImage);if(f){f.style.display="none"}}document.body.style.cursor="default";if(a.blockUI){d.unblock()}d.html(c)}}function s(){t(true,"");var b="http://picasaweb.google.com/data/feed/api/user/"+e.username+(e.album!==""?"/album/"+e.album:"")+"?kind=photo&max-results="+e.maxResults+"&alt=json&q="+(e.authKey!==""?"&authkey="+e.authKey:"")+(e.keyword!==""?"&tag="+e.keyword:"")+"&imgmax=d&thumbsize="+e.thumbSize+(e.thumbCrop==1?"c":"u")+","+e.photoSize;a.getJSON(b,"callback=?",n);return d}function r(){if(e.photostore[e.album]){m(e.photostore[e.album])}else{var b="http://picasaweb.google.com/data/feed/api/user/"+e.username+(e.album!==""?"/album/"+e.album:"")+"?kind=photo&alt=json"+(e.authKey!==""?"&authkey="+e.authKey:"")+(e.keyword!==""?"&tag="+e.keyword:"")+"&imgmax=d&thumbsize="+e.thumbSize+(e.thumbCrop==1?"c":"u")+","+e.photoSize;t(true,"");a.getJSON(b,"callback=?",m)}return d}function q(){if(e.albumstore.feed){l(e.albumstore)}else{t(true,"");var b="http://picasaweb.google.com/data/feed/api/user/"+e.username+"?kind=album&access="+e.albumTypes+"&alt=json&thumbsize="+e.albumThumbSize+(e.albumCrop==1?"c":"u");a.getJSON(b,"callback=?",l)}return d}function p(a){a.stopPropagation();a.preventDefault();e.onclickThumb(a)}function o(a){a.stopPropagation();a.preventDefault();e.onclickAlbumThumb(a)}function n(b){var c=a("<div/>"),d=b.feed?b.feed.entry.length:0,f=0;var g=e.username.replace(/\./g,"_");while(f<e.maxResults&&f<d){var h=k(b.feed.entry[f],false,g);c.append(h);f++}c.append("<div style='clear: both;height:0px;'> </div>");var i=a("div.pwi_photo",c).css(e.thumbCss);if(typeof e.popupExt==="function"){e.popupExt(i.find("a[rel='lb-"+g+"']"))}else if(typeof e.onclickThumb!="function"&&a.slimbox){i.find("a[rel='lb-"+g+"']").slimbox(e.slimbox_config,function(a){var b=a.title;if(a.parentNode.childElementCount>1){var c=a.parentNode.getElementsByClassName("downloadlink");if(c.length>0){var d='<a href="'+c[0].href+'">Download</a>';b=a.title+"  "+d}}return[a.href,b]})}t(false,c)}function m(b){var c,d,f="",g=b.feed.openSearch$totalResults.$t,i="",j="",l=b.feed.gphoto$location===undefined?"":b.feed.gphoto$location.$t,m,n=h(b.feed.gphoto$timestamp===undefined?"":b.feed.gphoto$timestamp.$t),o=g=="1"?false:true;if(b.feed.subtitle===undefined){m=""}else{var p=b.feed.subtitle.$t.match(/\[keywords\s*:\s*.*\s*\](.*)/);if(p){m=p[1]}else{m=b.feed.subtitle.$t}}i=b.feed.title==="undefined"||e.albumTitle.length>0?e.albumTitle:b.feed.title.$t;c=a("<div/>");if(e.mode!="album"&&e.mode!="keyword"){f=a("<div class='pwi_album_backlink'>"+e.labels.albums+"</div>").bind("click.pwi",function(a){a.stopPropagation();q();return false});c.append(f)}if(e.showAlbumDescription){d=a("<div class='pwi_album_description'/>");d.append("<div class='title'>"+i+"</div>");d.append("<div class='details'>"+g+" "+(o?e.labels.photos:e.labels.photo)+(e.showAlbumdate?", "+n:"")+(e.showAlbumLocation&&l?", "+l:"")+"</div>");d.append("<div class='description'>"+m+"</div>");if(e.showSlideshowLink){if(e.mode==="keyword"||e.keyword!==""){}else{d.append("<div><a href='http://picasaweb.google.com/"+e.username+"/"+b.feed.gphoto$name.$t+""+(e.authKey!==""?"?authkey="+e.authKey:"")+"#slideshow/"+b.feed.entry[0].gphoto$id.$t+"' rel='gb_page_fs[]' target='_new' class='sslink'>"+e.labels.slideshow+"</a></div>")}}c.append(d)}if(e.showSlideshow&&typeof e.popupExt==="function"){var s=!a.support.opacity&&!window.XMLHttpRequest;var u=a("<div class='pwi_photo'/>");if(s||navigator.userAgent.match(/(iPad)|(iPhone)|(iPod)/i)!=null){for(var v=0;v<b.feed.link.length;v++){if(b.feed.link[v].type=="text/html"&&b.feed.link[v].rel=="alternate"){u.append("<a class='iframe' href='"+b.feed.link[v].href+"#slideshow/' rel='sl-"+e.username+"' title='"+n+"'>"+e.labels.slideshow+"</a><br>");break}}}else{for(var v=0;v<b.feed.link.length;v++){if(b.feed.link[v].type=="application/x-shockwave-flash"){u.append("<a class='iframe' href='"+b.feed.link[v].href+"' rel='sl-"+e.username+"' title='"+n+"'>"+e.labels.slideshow+"</a><br>");break}}}c.append(u);c.append("<div style='clear: both;height:0px;'/>")}if(g>e.maxResults){$pageCount=g/e.maxResults;var w=a("<div class='pwi_prevpage'/>").text(e.labels.prev),x=a("<div class='pwi_nextpage'/>").text(e.labels.next);j=a("<div class='pwi_pager'/>");if(e.page>1){w.addClass("link").bind("click.pwi",function(a){a.stopPropagation();e.page=parseInt(e.page,10)-1;r();return false})}j.append(w);for(var y=1;y<$pageCount+1;y++){if(y==e.page){f="<div class='pwi_pager_current'>"+y+"</div> "}else{f=a("<div class='pwi_pager_page'>"+y+"</div>").bind("click.pwi",y,function(a){a.stopPropagation();e.page=a.data;r();return false})}j.append(f)}if(e.page<$pageCount){x.addClass("link").bind("click.pwi",function(a){a.stopPropagation();e.page=parseInt(e.page,10)+1;r();return false})}j.append(x);j.append("<div style='clear: both;height:0px;'/>")}if(j.length>0&&(e.showPager==="both"||e.showPager==="top")){c.append(j)}var z=(e.page-1)*e.maxResults;var A=e.maxResults*e.page;var B=e.username.replace(/\./g,"_");for(var v=0;v<g;v++){var C=k(b.feed.entry[v],!(v>=z&&v<A),B);c.append(C)}if(j.length>0&&(e.showPager==="both"||e.showPager==="bottom")){c.append(j.clone(true))}if(e.showPermaLink){c.append("<div style='clear: both;height:0px;'/>");var D=a("<div id='permalinkenable' class='pwi_nextpage'/>").text(e.labels.showPermaLink).bind("click.pwi",y,function(a){a.stopPropagation();var b=document.getElementById("permalinkbox");if(b){b.style.display="block"}b=document.getElementById("permalinkenable");if(b){b.style.display="none"}return false});var E=document.URL.split("?",2);var F=E[0]+"?pwi_album_selected="+b.feed.gphoto$name.$t+"&pwi_albumpage="+e.page;c.append(D);var G=a("<div style='display:none;' id='permalinkbox' />");var H=a("<form />");var I=a("<input type='text' size='40' name='PermaLink' readonly />").val(F);H.append(I);G.append(H);c.append(G)}e.photostore[e.album]=b;var J=a(".pwi_photo",c).css(e.thumbCss);if(typeof e.popupExt==="function"){e.popupExt(J.find("a[rel='lb-"+B+"']"));e.popupExt(J.find("a[rel='sl-"+B+"']"))}else if(typeof e.onclickThumb!="function"&&a.slimbox){J.find("a[rel='lb-"+B+"']").slimbox(e.slimbox_config,function(a){var b=a.title;if(a.parentNode.childElementCount>1){var c=a.parentNode.getElementsByClassName("downloadlink");if(c.length>0){var d='<a href="'+c[0].href+'">Download</a>';b=a.title+"  "+d}}return[a.href,b]})}c.append("<div style='clear: both;height:0px;'/>");t(false,c)}function l(b){var c=a("<div/>"),d=0;var f=0,g="",i=0;var j,k;if(navigator.userAgent.match(/(iPad)|(iPhone)|(iPod)/i)==null){j=new Date(e.albumStartDateTime);if(isNaN(j)){var m=e.albumStartDateTime.replace(/-/g,"/");j=new Date(m)}k=new Date(e.albumEndDateTime);if(isNaN(k)){var m=e.albumEndDateTime.replace(/-/g,"/");k=new Date(m)}}else{var m=e.albumStartDateTime.replace(/-/g,"/");j=new Date(m);m=e.albumEndDateTime.replace(/-/g,"/");k=new Date(m)}d=e.albumsPerPage*(e.albumPage-1);f=b.feed.entry.length;while(d<e.albumMaxResults&&d<f&&d<e.albumsPerPage*e.albumPage){var n=new Date(Number(b.feed.entry[d].gphoto$timestamp.$t)),o=b.feed.entry[d].media$group.media$thumbnail[0].url;if((a.inArray(b.feed.entry[d].gphoto$name.$t,e.albums)>-1||e.albums.length===0)&&(b.feed.entry[d].gphoto$albumType===undefined||a.inArray(b.feed.entry[d].gphoto$albumType.$t,e.removeAlbumTypes)==-1)&&(e.albumStartDateTime==""||n>=j)&&(e.albumEndDateTime==""||n<=k)){var p=true;if(e.albumKeywords.length>0){p=false;var q=b.feed.entry[d].summary.$t.match(/\[keywords\s*:\s*(.*)\s*\]/);if(q){var s=new Array;var u=q[1].split(/,/);for(var v in u){$newmatch=u[v].match(/\s*['"](.*)['"]\s*/);if($newmatch){s.push($newmatch[1])}}if(s.length>0){p=true;for(var v in e.albumKeywords){if(a.inArray(e.albumKeywords[v],s)<0){p=false;break}}}}}if(p==true){i++;if(e.showAlbumThumbs){$scAlbum=a("<div class='pwi_album' style='height:180px;"+(e.albumThumbAlign==1?"width:"+(e.albumThumbSize+1)+"px;":"")+"cursor: pointer;'/>")}else{$scAlbum=a("<div class='pwi_album' style='cursor: pointer;'/>")}var w=b.feed.entry[d];$scAlbum.bind("click.pwi",w,function(a){a.stopPropagation();e.page=1;e.album=a.data.gphoto$name.$t;if(typeof e.onclickAlbumThumb==="function"){e.onclickAlbumThumb(a);return false}else{r();return false}});if(e.showAlbumThumbs){$scAlbum.append("<img src='"+o+"'/>")}if(e.showAlbumTitles){$item_plural=b.feed.entry[d].gphoto$numphotos.$t=="1"?false:true;$scAlbum.append("<br/>"+(b.feed.entry[d].title.$t.length>e.showAlbumTitlesLength?b.feed.entry[d].title.$t.substring(0,e.showCaptionLength):b.feed.entry[d].title.$t)+"<br/>"+(e.showAlbumdate?h(b.feed.entry[d].gphoto$timestamp.$t):"")+(e.showAlbumPhotoCount?"    "+b.feed.entry[d].gphoto$numphotos.$t+" "+($item_plural?e.labels.photos:e.labels.photo):""))}c.append($scAlbum)}}d++}c.append("<div style='clear: both;height:0px;'/>");if(i==0){c=a("<div class='pwi_album_description'/>");c.append("<div class='title'>"+e.labels.noalbums+"</div>");t(false,c);return}if(f>e.albumsPerPage){var x=f/e.albumsPerPage;var y=a("<div class='pwi_prevpage'/>").text(e.labels.prev),z=a("<div class='pwi_nextpage'/>").text(e.labels.next);$navRow=a("<div class='pwi_pager'/>");if(e.albumPage>1){y.addClass("link").bind("click.pwi",function(a){a.stopPropagation();e.albumPage=parseInt(e.albumPage,10)-1;l(b);return false})}$navRow.append(y);for(var v=1;v<x+1;v++){if(v==e.albumPage){tmp="<div class='pwi_pager_current'>"+v+"</div> "}else{tmp=a("<div class='pwi_pager_page'>"+v+"</div>").bind("click.pwi",v,function(a){a.stopPropagation();e.albumPage=a.data;l(b);return false})}$navRow.append(tmp)}if(e.albumPage<x){z.addClass("link").bind("click.pwi",function(a){a.stopPropagation();e.albumPage=parseInt(e.albumPage,10)+1;l(b);return false})}$navRow.append(z);$navRow.append("<div style='clear: both;height:0px;'/>");if($navRow.length>0&&(e.showPager==="both"||e.showPager==="top")){c.append($navRow)}if($navRow.length>0&&(e.showPager==="both"||e.showPager==="bottom")){c.prepend($navRow.clone(true))}}e.albumstore=b;t(false,c)}function k(b,c,d){var f,g="",h="";h=i(b.summary?b.summary.$t:"");if(e.showPhotoDate){if(b.exif$tags.exif$time){g=j(b.exif$tags.exif$time.$t)}else if(b.gphoto$timestamp){g=j(b.gphoto$timestamp.$t)}else{g=j(b.published.$t)}g+=" "}if(c){f=a("<div class='pwi_photo' style='display: none'/>");f.append("<a href='"+b.media$group.media$thumbnail[1].url+"' rel='lb-"+d+"' title='"+g+"'></a>");if(e.showPhotoDownloadPopup){var k=a("<div style='display: none'/>");k.append("<a class='downloadlink' href='"+b.media$group.media$content[0].url+"'/>");f.append(k)}return f}else{g+=h.replace(new RegExp("'","g"),"&#39;");f=a("<div class='pwi_photo' style='height:"+(e.thumbSize+(e.showPhotoCaption?15:1))+"px;"+(e.thumbAlign==1?"width:"+(e.thumbSize+1)+"px;":"")+"cursor: pointer;'/>");f.append("<a href='"+b.media$group.media$thumbnail[1].url+"' rel='lb-"+d+"' title='"+g+"'><img src='"+b.media$group.media$thumbnail[0].url+"'/></a>");if(e.showPhotoDownloadPopup){var k=a("<div style='display: none'/>");k.append("<a class='downloadlink' href='"+b.media$group.media$content[0].url+"'/>");f.append(k)}if(e.showPhotoCaption){if(e.showPhotoCaptionDate&&e.showPhotoDate){h=g}if(e.showPhotoDownload){h+='<a href="'+b.media$group.media$content[0].url+'">'+e.labels.downloadphotos+"</a>"}if(h.length>e.showCaptionLength){h=h.substring(0,e.showCaptionLength)}f.append("<br/>"+h)}if(typeof e.onclickThumb==="function"){var l=b;f.bind("click.pwi",l,p)}return f}}function j(a){var b=new Date(Number(a));$year=b.getUTCFullYear();if($year<1e3){$year+=1900}if(b=="Invalid Date"){return a}else{if(b.getUTCHours()==0&&b.getUTCMinutes()==0&&b.getUTCSeconds()==0){return b.getUTCDate()+"-"+(b.getUTCMonth()+1)+"-"+$year}else{return b.getUTCDate()+"-"+(b.getUTCMonth()+1)+"-"+$year+" "+b.getUTCHours()+":"+(b.getUTCMinutes()<10?"0"+b.getUTCMinutes():b.getUTCMinutes())}}}function i(a){return a.replace(/\n/g,"<br />\n")}function h(a){var b=new Date(Number(a)),c=b.getUTCFullYear();if(c<1e3){c+=1900}return b.getUTCDate()+"-"+(b.getUTCMonth()+1)+"-"+c}function g(){if(e.username===""){alert("Make sure you specify at least your username."+"\n"+"See http://pwi.googlecode.com for more info");return}if(e.useQueryParameters){var a=document.URL.split("?",2);if(a.length==2){var b=a[1].split("&");var c=false;var d=1;for($queryParam in b){var f=b[$queryParam].split("=",2);if(f.length==2){if(f[0]=="pwi_album_selected"){e.mode="album";e.album=f[1];c=true}if(f[0]=="pwi_albumpage"){d=f[1]}if(f[0]=="pwi_showpermalink"){e.showPermaLink=true}}}if(c){e.page=d;e.showPermaLink=false}}}switch(e.mode){case"latest":s();break;case"album":r();break;case"keyword":r();break;default:q();break}}function f(){e=c;ts=(new Date).getTime();e.id=ts;d=a("<div id='pwi_"+ts+"'/>").appendTo(b);d.addClass("pwi_container");g();return false}var d,e={};c=a.extend({},a.fn.pwi.defaults,c);b=this;f()};a.fn.pwi.defaults={mode:"albums",username:"",album:"",authKey:"",albums:[],keyword:"",albumKeywords:[],albumStartDateTime:"",albumEndDateTime:"",albumCrop:1,albumTitle:"",albumThumbSize:160,albumThumbAlign:1,albumMaxResults:999,albumsPerPage:999,albumPage:1,albumTypes:"public",page:1,photoSize:800,maxResults:50,showPager:"bottom",thumbSize:72,thumbCrop:0,thumbAlign:0,thumbCss:{margin:"5px"},onclickThumb:"",onclickAlbumThumb:"",popupExt:"",removeAlbumTypes:[],showAlbumTitles:true,showAlbumTitlesLength:9999,showAlbumThumbs:true,showAlbumdate:true,showAlbumPhotoCount:true,showAlbumDescription:true,showAlbumLocation:true,showSlideshow:true,showSlideshowLink:false,showPhotoCaption:false,showPhotoCaptionDate:false,showCaptionLength:9999,showPhotoDownload:false,showPhotoDownloadPopup:false,showPhotoDate:true,showPermaLink:false,useQueryParameters:true,loadingImage:"",labels:{photo:"photo",photos:"photos",downloadphotos:"Download photos",albums:"Back to albums",slideshow:"Display slideshow",noalbums:"No albums available",loading:"PWI fetching data...",page:"Page",prev:"Previous",next:"Next",showPermaLink:"Show PermaLink",devider:"|"},months:["January","February","March","April","May","June","July","August","September","October","November","December"],slimbox_config:{loop:false,overlayOpacity:.6,overlayFadeDuration:400,resizeDuration:400,resizeEasing:"swing",initialWidth:250,initlaHeight:250,imageFadeDuration:400,captionAnimationDuration:400,counterText:"{x}/{y}",closeKeys:[27,88,67,70],prevKeys:[37,80],nextKeys:[39,83]},blockUIConfig:{message:"<div class='lbLoading pwi_loader'>loading...</div>",css:"pwi_loader"},albumstore:{},photostore:{},token:""}})(jQuery)

