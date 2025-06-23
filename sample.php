<?php

session_start();

use SergiX44\Nutgram\Exception\InvalidDataException;
use Respect\Validation\Validator as v;
use SleekDB\Store;
use App\Fields;

$bot = require_once __DIR__ . '/bootstrap.php';

if( isset( $_SESSION[ 'logged-in' ] ) && $_SESSION[ 'logged-in' ] == true ) {
    
    $query = [];
    $query[ 'site_id' ] = $_GET[ 'site_id' ] ?? null;
    $query[ 'sample_id' ] = $_GET[ 'sample_id' ] ?? null;
    $query[ 'action' ] = $_GET[ 'action' ] ?? null;
    unset( $_GET[ 'site_id' ], $_GET[ 'sample_id' ], $_GET[ 'action' ] );
    
    if( !empty( $_GET ) ) {
        
        $query = http_build_query( $query );
        $query = !empty( $query ) ? "?{$query}" : $query;
        
        header( "Location: sample.php{$query}", true, 302 );
        exit;
        
    } else {
        
        extract( $query );
        
        $siteStore = new Store('site', DB_PATH, ['timeout' => false]);
        
        if( $site_id === null && $sample_id === null ) {
            
            $sites = $siteStore->findBy(
                [
                    [ 'accepted', '=', true ],
                    [ 'user_ids', 'CONTAINS', (int) $_ENV[ 'ADMIN_ID' ] ]
                ],
                [ '_id' => 'asc' ]
            );
            
            include __DIR__ . '/template/site.php';
            
        } else {
            
            $is_vaild_site_id = v::intVal()->positive()->validate( $site_id );
            $is_vaild_sample_id = v::nullable( v::oneOf( v::intVal()->positive(), v::StartsWith('new-') ) )->validate( $sample_id );
            $is_vaild_action = v::nullable( v::oneOf( v::equals('copy'), v::equals('delete') ) )->validate( $action );
            
            if( !$is_vaild_site_id || !$is_vaild_sample_id || !$is_vaild_action ) {
                
                exit('Invalid request!');
                
            }
            
            $sampleStore = new Store('sample', DB_PATH, ['timeout' => false]);
            
            $site = $siteStore->findOneBy(
                [
                    [ 'accepted', '=', true ],
                    [ '_id', '==', $site_id ],
                    [ 'user_ids', 'CONTAINS', (int) $_ENV[ 'ADMIN_ID' ] ]
                ]
            );
            
            if( $site === null ) {
                
                exit('Invalid request!');
                
            } 
            
            if( $sample_id === null ) {
                
                $site = $siteStore
                    ->createQueryBuilder()
                    ->disableCache()
                    ->where( [ "_id", "=", $site[ "_id" ] ] )
                    ->where( [ 'accepted', '=', true ] )
                    ->where( [ 'user_ids', 'CONTAINS', (int) $_ENV[ 'ADMIN_ID' ] ] )
                    ->join( function( $site ) use ( $sampleStore ) {
                        return $sampleStore->findBy( [ "site_id", "=", $site[ "_id" ] ], [ '_id' => 'asc' ] );
                    }, "samples" )
                    ->getQuery()
                    ->first();
                    
                $fields = new Fields;
                
                include __DIR__ . '/template/samples.php';
                
            } else {
                
                if( $_SERVER[ 'REQUEST_METHOD' ] === 'POST' ) {
                    
                    $data = [];
                    
                    $data[ 'name' ] = htmlspecialchars( strip_tags( trim( $_POST[ 'name' ] ) ) );
                    $data[ 'title' ] = htmlspecialchars( strip_tags( trim( $_POST[ 'title' ] ) ) );
                    $data[ 'slug' ] = !empty( trim( $_POST[ 'slug' ] ) ) ? htmlspecialchars( strip_tags( trim( $_POST[ 'slug' ] ) ) ) : '';
                    $data[ 'content' ] = !empty( trim( $_POST[ 'content' ] ) ) ? trim( $_POST[ 'content' ] ) : '';
                    
                    if( !empty( $_POST[ 'tax' ] ) ) {
                        
                        foreach( $_POST[ 'tax' ] as $key => $value ) {
                            
                            $data[ 'tax' ][ $key ] = !empty( trim( $value ) ) ? htmlspecialchars( strip_tags( trim( $value ) ) ) : '';
                            
                        }
                        
                    }
                    
                    if( !empty( $_POST[ 'cf' ] ) ) {
                        
                        foreach( $_POST[ 'cf' ] as $key => $value ) {
                            
                            $data[ 'cf' ][ $key ] = !empty( trim( $value ) ) ? htmlspecialchars( strip_tags( trim( $value ) ) ) : '';
                            
                        }
                        
                    }
                    
                }
                
                if( str_starts_with( $sample_id, 'new-' ) ) {
                    
                    [, $sample_group] = explode( '-', $sample_id, 2 );
                    
                    if( ! in_array( $sample_group, Fields::GROUPS ) ) {
                        
                        exit('Invalid request!');
                        
                    } else {
                        
                        if( $action !== null ) {
                            
                            exit('Invalid request!');
                            
                        }
                        
                        if( isset( $data ) ) {
                            
                            $data[ 'group' ] = $sample_group;
                            $data[ 'site_id' ] = $site[ "_id" ];
                            
                            $sample = $sampleStore->insert( $data );
                            
                            header( "Location: sample.php?site_id={$site_id}&sample_id={$sample[ '_id' ]}", true, 302 );
                            exit;
                            
                        }
                        
                        $sample = [];
                        $group_name = Fields::getGroupName( $sample_group );
                        $fields = (new Fields( $sample_group ))->getFields();
                        
                        include __DIR__ . '/template/sample.php';
                        
                    }
                    
                } else {
                    
                    $sample = $sampleStore->findById( $sample_id );
                    
                    if( $sample === null ) {
                        
                        exit('Invalid request!');
                        
                    } else {
                        
                        if( isset( $data ) ) {
                            
                            $sample = $sampleStore->updateById( $sample[ '_id' ], $data );
                            
                            unset( $data, $key, $value );
                            
                        } else {
                            
                            if( $action !== null ) {
                                
                                switch( $action ) {
                                    
                                    case 'copy':
                                    
                                        unset( $sample[ '_id' ] );
                                        $sample[ 'name' ] = $sample[ 'name' ] . ' (کپی)';
                                        
                                        $sample = $sampleStore->insert( $sample );
                                    
                                    break;
                                    
                                    case 'delete':
                                    
                                        $sampleStore->deleteById( $sample_id );
                                        
                                        header( "Location: sample.php?site_id={$site_id}", true, 302 );
                                        exit;
                                    
                                    break;
                                    
                                    default:
                                    
                                        exit('Invalid request!');
                                    
                                }
                                
                            }
                            
                        }
                        
                        $sample_group = $sample[ 'group' ];
                        $group_name = Fields::getGroupName( $sample_group );
                        $fields = (new Fields( $sample_group ))->getFields();
                        
                        include __DIR__ . '/template/sample.php';
                        
                    }
                    
                }
                
            }
            
        }
        
    }
    
} else {
    
    try {
        
        $query = [];
        $query[ 'site_id' ] = $_GET[ 'site_id' ] ?? null;
        $query[ 'sample_id' ] = $_GET[ 'sample_id' ] ?? null;
        $query = http_build_query( $query );
        $query = !empty( $query ) ? "?{$query}" : $query;
        unset( $_GET[ 'site_id' ], $_GET[ 'sample_id' ] );
        
        if( empty( $_GET[ 'hash' ] ) ) {
            
            throw new InvalidDataException('hash is empty!');
            
        }
        
        $auth_query = http_build_query( $_GET );
        
        $login_data = $bot->validateLoginData( $auth_query );
        
        if( $login_data->id != $_ENV[ 'ADMIN_ID' ] ) {
            
            throw new InvalidDataException('User id is invalid');
            
        }
        
        $_SESSION[ 'logged-in' ] = true;
        
        header( "Location: sample.php{$query}", true, 302 );
        exit;
        
    } catch (InvalidDataException $e) {
        
        exit('Invalid request!');
        
    }
    
}