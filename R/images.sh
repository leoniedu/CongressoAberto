# convert foto108355.jpg -colorspace Gray -resize 60x  \
#           -bordercolor white  -border 6 \
#           -bordercolor grey60 -border 1 \
#           -background  none   -rotate 6 \
#           -background  black  \( +clone -shadow 60x4+4+4 \) +swap \
#           -background  none   -flatten \
#           poloroid.png


## from http://www.imagemagick.org/Usage/thumbnails/
## FIX: some originals are png files?

# find . -type f -name 'foto*.jpg' -exec convert   -resize 200x -colorspace Gray -bordercolor snow -background black +polaroid {} {}_polaroid.png \;
# for FILE in `find . -name "foto*jpg*polaroid*"`
# do
#     NEW="`echo $FILE | sed -e 's/.jpg//'`"
#     mv $FILE "polaroid/$NEW"
#     echo $NEW
# done

# resizing party logos
for FILE in `find . -name "*.jpg"`
do 
    NEW="`echo $FILE | sed -e 's/.jpg/_r.jpg/'`"
    convert   -resize 300x300 $FILE $NEW
    mv $NEW "resized/$FILE"
    echo $NEW
done

for FILE in `find . -name "*.gif"`
do 
    NEW="`echo $FILE | sed -e 's/.gif/.jpg/'`"
    convert   -resize 300x300 $FILE $NEW
    ##mv $NEW "resized/$NEW"
    echo $NEW
done
    
## creating a picture combining several files
##http://www.imagemagick.org/Usage/layers/
# convert \( foto123000.jpg foto123001.jpg +append \) \
#           \( foto123002.jpg foto123003.jpg +append \) \
#           -background none -append   append_array.jpg



for FILE in `find . -name "foto*.jpg"`
do
    NEW="`echo $FILE | sed -e 's/.jpg/polaroid.png/'`"
    convert   -resize 200x  -bordercolor snow -background black +polaroid $FILE $NEW
    mv $NEW "polaroid/$NEW"
    ##mv $FILE "processed/$FILE"
    echo $NEW
done


for FILE in `find . -name "foto*.jpg"`
do
    NEW="`echo $FILE | sed -e 's/.jpg/polaroid4.png/'`"
    convert   -resize 150x  -bordercolor snow -background black +polaroid $FILE $NEW
    mv $NEW "polaroid/$NEW"
    ##mv $FILE "processed/$FILE"
    echo $NEW
done


for FILE in `find . -name "foto*.jpg"`
do
    NEW="`echo $FILE | sed -e 's/.jpg/polaroid2.png/'`"
    convert   -resize 200x  -colors 256  -bordercolor snow -background black +polaroid $FILE $NEW
    mv $NEW "polaroid/$NEW"
    ##mv $FILE "processed/$FILE"
    echo $NEW
done



for FILE in `find . -name "foto*.jpg"`
do
    NEW="`echo $FILE | sed -e 's/.jpg/polaroid3.png/'`"
    convert   -resize 200x  -bordercolor snow -background black +polaroid $FILE $NEW
    mv $NEW "polaroid/$NEW"
    ##mv $FILE "processed/$FILE"
    echo $NEW
done

for FILE in `find . -name "foto*.jpg"`
do
    NEW="`echo $FILE | sed -e 's/.jpg/polaroid.jpg/'`"
    convert   -resize 200x  -bordercolor snow -background white +polaroid $FILE $NEW
    mv $NEW "polaroid/$NEW"
    ##mv $FILE "processed/$FILE"
    echo $NEW
done
##-colorspace Gray
##

for FILE in `find . -name "foto*.jpg"`
do
    NEW="`echo $FILE | sed -e 's/.jpg/polaroid.gif/'`"
    convert   -resize 200x -bordercolor snow -background white +polaroid $FILE $NEW
    mv $NEW "polaroid/$NEW"
    ##mv $FILE "processed/$FILE"
    echo $NEW
done


# for FILE in `find . -name "*"`
# do
#     NEW=`echo $FILE.png`
#     mv "$FILE" "$NEW"
#     echo $NEW
# done
