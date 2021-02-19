<?php

    $response['status'] = 'error';

    if($_FILES['ProductImage']['name'][0] == '')
    {
        $response['message'] = 'Choose files to upload';
    }
    else
    {
        for($i = 0, $UploadedPics = 0; $i < count($_FILES['ProductImage']['name']); $i++)
        {
            $ProductImage = $_FILES['ProductImage']['tmp_name'][$i];
            $ProductImageSize = $_FILES['ProductImage']['size'][$i];
            $ProductImageName = $_FILES['ProductImage']['name'][$i];
            $ProductImageExt = strtolower(substr(strrchr($ProductImageName,'.'),1));

            if(array_search(strtolower($ProductImageExt), $AllowedImageTypes) === false)
            {
                $response['message'] = 'Invalid File type. Only '.implode(', ', $AllowedImageTypes).' files are allowed ';
            }
            else
            {
                $FileName = $_POST['ProductID'].'_'.time().'_'.$i.'.'.$ProductImageExt;
                $ImageDir = _ROOT._ProductImageDir;
                $ImageThumbDir = _ROOT._ProductImageThumbDir;

                list($currwidth, $currheight, $type, $attr) = getimagesize($ProductImage);

                if($currwidth <= _ProductImageWidth)
                    $SetImageWidth = $currwidth;
                else
                    $SetImageWidth = _ProductImageWidth;

                if($currwidth <= _ProductImageThumbWidth)
                    $SetImageThumbWidth = $currwidth;
                else
                    $SetImageThumbWidth = _ProductImageThumbWidth;

                if(move_uploaded_file($ProductImage, $ImageDir.$FileName))
                {
                    //	GENERATE STANDARD SIZED IMAGE
        	        if(CreateThumbnail($ImageDir.$FileName, $ImageDir, $SetImageWidth))
    	            {
    	                //	GENERATE THUMB IMAGE
    					if(CreateThumbnail($ImageDir.$FileName, $ImageThumbDir, $SetImageThumbWidth))
    					{
                            $res = MysqlQuery("INSERT INTO product_images (ProductID, FileName, AddedOn) VALUES ('".$_POST['ProductID']."', '".$FileName."', '".time()."')");

                            if(MysqlAffectedRows() < 0)
                            {
                                @unlink($ImageDir.$FileName);
                                @unlink($ImageThumbDir.$FileName);
                                $response['message'] = 'Error occurred while uploading pictures. [LN 10]';
                            }
                            else
                            {
                                $InsertedID = MysqlInsertID();
                                $UploadedPics++;
                                ob_start();
                            ?>

                                <!-- Product Item Start -->
                                <div class="product">
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <div class="product-item">
                                            <!-- Product Image -->
                                            <figure class="product-grid-image">
                                                <img class="set-default<?=$SetDefault?>" src="/images/mark-default.png" title="Default Image">
                                                <a href="<?=_ProductImageDir.$FileName?>">
                                                    <img class="img-responsive" src="<?=_ProductImageDir.$FileName?>">
                                                </a>
                                                <!-- Product Buttons -->
                                                <div class="figure-caption">
                                                    <ul class="icons">
                                                        <li>
                                                            <a class="default-pic<?=$DefaultOption?>" href="javascript:void(0)" data-id="<?=$_POST['ProductID']?>" data-pk="<?=$InsertedID?>" data-opt="product_image" onclick="SetDefaultImage(this)"><i class="fa fa-check"></i></a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0)" data-id="<?=$InsertedID?>" data-opt="product_image" onclick="DeletejFilerThumbnail(this)"><i class="fa fa-trash"></i></a>
                                                    	</li>
                                                    </ul>
                                                </div>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                                <!-- Product Item Start -->

                            <?php
                                $InsertedProducts .= ob_get_contents();
                                ob_end_clean();
                            }
                        }
                        else
                        {
                            $response['message'] = 'Error occurred while uploading pictures. [LN 40]';
                        }
                    }
                    else
                    {
                        $response['message'] = 'Error occurred while uploading pictures. [LN 50]';
                    }
                }
                else
                {
                    $response['message'] = 'Error occurred while uploading pictures. [LN 60]';
                }
            }
        }

        if($UploadedPics)
        {
            RecordAdminActivity('Product Pictures Added', 'product_images', $_POST['ProductID'], $UploadedPics.' Pictures uploaded');
            $response['status'] = 'success';
            $response['html'] = $InsertedProducts;
        }
    }

    echo json_encode($response);
?>