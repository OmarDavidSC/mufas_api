<?php 

namespace App\Repositories;

use App\Utilities\FG;
use App\Models\StorageFile;
use App\Repositories\MeetingRepository;
use App\Models\RecordingFile;

class LocalhostRepository {

    public function createStorageFileByMeetingId(int $meeting_id) : ?array {
        $response = FG::responseDefault();
        try {
            $meetingRepository = new MeetingRepository();
            $meeting = $meetingRepository->getMeetingById($meeting_id);
            
            if (!$meeting) {
                throw new \Exception('No se logro crear la reunión');
            }
            $partial_uploads = 0;
            $total_uploads = 0;
            $storage_files = [];
            foreach ($meeting->recordings as $k1 => $recording) {

                foreach ($recording->recording_files as $k2 => $recording_file) {
                    $total_uploads ++;
                    if ($recording_file->status == 'completed') {
                        $storage_file = null;
                        
                        if ($recording_file->storage_file_id) {
                            $storage_file = StorageFile::where('id', $recording_file->storage_file_id)->first();
                        }

                        if (is_null($storage_file)) {
                            $storage_file = new StorageFile();
                        }
                        $patharray = explode('/', $recording_file->file_uri);

                        $storage_file->name = $recording_file->name;
                        $storage_file->path = array_pop($patharray);
                        $storage_file->uri = $recording_file->file_uri;
                        $storage_file->datetime_at = FG::getDateHour();
                        $storage_file->type = strtolower($recording_file->file_type);
                        $storage_file->size = $recording_file->file_size;
                        $storage_file->size_label = FG::getZiseConvert($recording_file->file_size);
                        $storage_file->format = strtolower($recording_file->file_extension);
                        $storage_file->bucket = 'localhost';
                        $storage_file->company_id = $meeting->company_id;
                        $storage_file->save();
                        $recording_file_model = RecordingFile::where('id', intval($recording_file->id))->first();
                        if ($recording_file_model) {
                            $recording_file_model->storage_file_id = $storage_file->id;
                            $recording_file_model->save();
                        }
                        $storage_files[] = $storage_file;
                        $partial_uploads++;
                    }
                }    
            }

            if ($partial_uploads != $total_uploads) {
                throw new \Exception('Todavía no se han subido todos los archivos de la grabación procesada.');
            }
                
            $response['success'] = true;
            $response['data'] = compact('storage_files');
            $response['message'] = 'Se subio correctamente';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        FG::debug($response);
        return $response;
    }

}