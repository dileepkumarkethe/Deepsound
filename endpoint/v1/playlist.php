<?php
if ($option == 'get-public-playlists') {
    if (IS_LOGGED == false) {
        $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
        echo json_encode($data);
        exit();
    }

    $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
    $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
    $playlists          = GetPlaylists(0,$limit,$offset);
    $data['status']     = 200;
    $data['playlists']  = $playlists['data'];
    $data['count']      = $playlists['count'];
}

if ($option == 'get-playlists') {
    if (IS_LOGGED == false) {
        $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
        echo json_encode($data);
        exit();
    }
    if (!empty($_REQUEST['id'])) {
        if (is_numeric($_REQUEST['id'])) {
            $id                 = secure($_REQUEST['id']);
            $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
            $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
            $playlists          = GetPlaylists($id,$limit,$offset);
            $data['status']     = 200;
            $data['playlists']  = $playlists['data'];
            $data['count']      = $playlists['count'];
        }
    }else{
        $data = array('status' => 400, 'error' => "Please check your details");
        echo json_encode($data);
        exit();
    }
}

if ($option == 'add-to-playlist') {
    if (IS_LOGGED == false) {
        $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
        echo json_encode($data);
        exit();
    }
	if (!empty($_REQUEST['playlists']) && !empty($_REQUEST['id'])) {
		$_REQUEST['playlists'] = secure($_REQUEST['playlists']);
		$songData = songData($_REQUEST['id']);
		if (!empty($songData)) {
			$explodePlaylistIDS = explode(',', $_REQUEST['playlists']);
			if (!empty($explodePlaylistIDS)) {
				foreach ($explodePlaylistIDS as $key => $playlist) {
					if (!empty($playlist) && is_numeric($playlist)) {
						$playlist = $music->playlist = getPlayList($playlist);
						$checkIfSongInPlayList = $db->where('track_id', $songData->id)->where('playlist_id', $playlist->id)->getValue(T_PLAYLIST_SONGS, 'count(*)');
						if (empty($checkIfSongInPlayList)) {
							$addSong = [
								'track_id' => $songData->id,
								'user_id' => $user->id,
								'time' => time(),
								'playlist_id' => $playlist->id
							];
							$insert = $db->insert(T_PLAYLIST_SONGS, $addSong);
						}
					}
				}
				$data['status'] = 200;
			} else {
				$data['status'] = 300;
			}
		}
	}else{
        $data = array('status' => 400, 'error' => "Please check your details");
        echo json_encode($data);
        exit();
    }
}

if ($option == 'create') {
	if (IS_LOGGED == false) {
        $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
        echo json_encode($data);
        exit();
	}
	if (!empty($_POST)) {
	    if (empty($_FILES['avatar']) || empty($_POST['name']) || !isset($_POST['privacy'])) {
	        $errors[] = "Please check your details";
	    } else {
	        $name        = secure($_POST['name']);
	        $privacy     = secure($_POST['privacy']);
	        $file_info   = array(
		        'file' => $_FILES['avatar']['tmp_name'],
		        'size' => $_FILES['avatar']['size'],
		        'name' => $_FILES['avatar']['name'],
		        'type' => $_FILES['avatar']['type'],
		        'crop' => array(
		            'width' => 600,
		            'height' => 600
		        )
		    );
		    $thumbnail = '';
		    $file_upload = shareFile($file_info);
		    if (!empty($file_upload['filename'])) {
		        $thumbnail = secure($file_upload['filename'], 0);
		    }
		    if (empty($thumbnail) || !file_exists($thumbnail)) {
		    	$errors[] = "Error found while uploading the playlist avatar, Please try again later.";
		    }
		    $privacy = 0;
		    if (isset($_POST['privacy'])) {
	            if (in_array($_POST['privacy'], array(0, 1))) {
	                $privacy = secure($_POST['privacy']);
	            }
	        }
	        if (empty($errors)) {
	        	$uid = generateKey(12, 12);
	           	$create_playList = [
	           		'uid' => $uid,
	           		'name' => $name,
	           		'user_id' => $user->id,
	           		'privacy' => $privacy,
	           		'thumbnail' => $thumbnail,
	           		'time' => time()
	           	];
	           	$create = $db->insert(T_PLAYLISTS, $create_playList);
	           	if ($create) {
	           		$data['status'] = 200;
                    $data['data'] = getPlayList($create);
	           	}
	        }
	    }
	}else{
        $data = array('status' => 400, 'error' => "Please check your details");
        echo json_encode($data);
        exit();
    }
}

if ($option == 'get-playlist-songs') {
	if (!empty($_REQUEST['id'])) {
		if (is_numeric($_REQUEST['id'])) {
			$playlist = $music->playlist = getPlayList($_REQUEST['id']);
			if (!empty($playlist)) {
			 	$getPlaylistSongs = $db->where('playlist_id', $playlist->id)->get(T_PLAYLIST_SONGS, null, ['track_id']);
			 	$final_ids = [];
			 	if (!empty($getPlaylistSongs)) {
			 		foreach ($getPlaylistSongs as $key => $song) {
			 			$songData = songData($song->track_id);
			 			if (!empty($songData)) {
			 				$final_ids[] = $songData;
			 			}
			 		}
			 	    $data['status'] = 200;
			 	    $data['songs'] = array_reverse($final_ids);
			 	}else{
                    $data = array('status' => 200, 'songs' => []);
                    echo json_encode($data);
                    exit();
                }
			}
		}
	}else{
        $data = array('status' => 400, 'error' => "Please check your details");
        echo json_encode($data);
        exit();
    }
}

if ($option == 'delete-playlist') {
	if (IS_LOGGED == false) {
        $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
        echo json_encode($data);
        exit();
	}
	if (!empty($_REQUEST['id'])) {
		if (is_numeric($_REQUEST['id'])) {
			$playlist = $music->playlist = getPlayList($_REQUEST['id']);
			if (!empty($playlist)) {
			 	if (isAdmin() || $user->id == $playlist->user_id) {
	           		$delete = $db->where('id', $playlist->id)->delete(T_PLAYLISTS);
	           		$delete = $db->where('playlist_id', $playlist->id)->delete(T_PLAYLIST_SONGS);
		           	if ($delete) {
		           		$data['status'] = 200;
		           	}
	           	}
			}else{
                $data = array('status' => 400, 'error' => 'no playlist found with this id');
                echo json_encode($data);
                exit();
            }
		}
	}else{
        $data = array('status' => 400, 'error' => "Please check your details");
        echo json_encode($data);
        exit();
    }
}

if ($option == 'update') {
    if (IS_LOGGED == false) {
        $data = array('status' => 400, 'error' => 'You ain\'t logged in!');
        echo json_encode($data);
        exit();
    }
	if (!empty($_REQUEST['id'])) {
		if (is_numeric($_REQUEST['id'])) {
			$playlist = $music->playlist = getPlayList($_REQUEST['id']);
			if (!empty($playlist)) {
			 	if (empty($_POST['name']) || !isset($_POST['privacy'])) {
			        $errors[] = "Please check your details";
			    } else {
			    	$thumbnail   = $playlist->thumbnail;
			        $name        = secure($_POST['name']);
			        $privacy     = secure($_POST['privacy']);
			        if (!empty($_FILES['avatar'])) {
			        	$file_info   = array(
					        'file' => $_FILES['avatar']['tmp_name'],
					        'size' => $_FILES['avatar']['size'],
					        'name' => $_FILES['avatar']['name'],
					        'type' => $_FILES['avatar']['type'],
					        'crop' => array(
					            'width' => 600,
					            'height' => 600
					        )
					    );
					    $file_upload = shareFile($file_info);
					    if (!empty($file_upload['filename'])) {
					        $thumbnail = secure($file_upload['filename'], 0);
					    }
			        }
				    if (empty($thumbnail) || !file_exists($thumbnail)) {
				    	$errors[] = "Error found while updating the playlist avatar, Please try again later.";
				    }
				    $privacy = $playlist->privacy;
				    if (isset($_POST['privacy'])) {
			            if (in_array($_POST['privacy'], array(0, 1))) {
			                $privacy = secure($_POST['privacy']);
			            }
			        }
			        if (empty($errors)) {
			           	$update_playList = [
			           		'name' => $name,
			           		'privacy' => $privacy,
			           		'thumbnail' => $thumbnail,
			           	];
			           	if (isAdmin() || $user->id == $playlist->user_id) {
			           		$update = $db->where('id', $playlist->id)->update(T_PLAYLISTS, $update_playList);
				           	if ($update) {
				           		$data['status'] = 200;
				           	}
			           	}
			        }
			    }
			}
		}
	}else{
        $data = array('status' => 400, 'error' => "Please check your details");
        echo json_encode($data);
        exit();
    }
}