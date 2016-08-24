/**
 * Created by Bagdad on 23.08.2016.
 */

var fs = require('fs');
//var path = require('path');
function getFilesRecursive (folder) {
    var fileContents = fs.readdirSync(folder),
        fileTree = [],
        stats;

    fileContents.forEach(function (fileName) {
        stats = fs.lstatSync(folder + '/' + fileName);

        if (stats.isDirectory()) {
            //fileTree.push({
            //    name: fileName,
            //    children: getFilesRecursive(folder + '/' + fileName)
            //});
        } else {
            fileTree.push({
                name: fileName
            });
        }
    });

    return fileTree;
};

console.log(__dirname);
console.log(getFilesRecursive(__dirname));
/*
walk(__dirname, function(err, results) {
  if (err) throw err;
  console.log(results);
});
*/
