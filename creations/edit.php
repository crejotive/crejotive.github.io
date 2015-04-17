<!DOCTYPE html>
<html>
<head>
<title>Edit</title>
<style>
.plus {
  background-color: red;
  width: 20px;
  height: 20px;
}
.creations {
  background-color: yellow;
}
.thumbs {
  background-color: lightgray;
}
.aspectRatio {
  background-color: teal;
}
.URL {
  background-color: blue;
}
.name {
  background-color: green;
}
.descr {
  background-color: goldenrod;
}
</style>
</head>
<body>
<table><tbody>
<tr colspan=3><td class="creations"><?php /* thumbs des cre */ ?><div class="plus" id="addcre"></div>
<tr><td class="thumbs" rowspan=2><?php /* thumbs des images */ ?><div class="plus" id="addimg"></div>
<td class="aspectRatio">
<input type="radio">800 x 600</input><br>
<input type="radio">600 x 600</input><br>
<input type="radio">square</input><br>
<input type="radio">free</input>
<td class="thumbnail">
<input type="radio">thumbnail</input><br>
<img id="thumb">
<tr class="canvas" colspan=2><td colspan=2><canvas width="100%" height="100%"></canvas>
<tr><td class="URL">keyword<td colspan=2><input></input>
<tr><td class="name">name<td colspan=2><input></input>
<tr><td class="descr">description<td colspan=2><textarea></textarea>
</tbody></table>
</body>
</html>
