# cohtools
Tools for City of Heroes

This is designed to work alongside the existing Xampp setup when you install from this page https://wiki.ourodev.com/view/Account_Portal

# install
1) Install the account portal from the previously linked page above
2) Copy the contents of this repo into the htdocs folder to use it
3) (optional) Update the existing index.php to point to the textureMenu.php page

# usage
1) You need to extract the contents of the pigg file using an existing tool such as https://git.ourodev.com/CoX/Piglet
2) The you will need to go to the page such as http://localhost/textureMenu.php
3) Upload a texture file from the extracted Pigg. This auto splits it into a header and image which you can download
4) Modify the image with a tool like Gimp which handles dds files
5) Upload the new image which will automatically create a new texture file to download
6) Add the updated texture file into the extracted pigg folder structure (back to its original spot)
7) Re-Pigg the folder
8) Rename the original pigg file in your CoH dir to <whatever>.bak
9) Put the new pigg file in the CoH dir and start the game


