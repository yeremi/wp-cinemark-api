<?php
/**
 * Copyright (C) 2020 Yeremi Felix Loli Saquiray
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (!defined('ABSPATH')) exit;

/**
 * @return mixed
 */
function get_movies_cinemark()
{
    // For testing
    // $decode = file_get_contents(CINEMARK_API_PATH . '/assets/js/movies.js');
    // $movies = json_decode($decode, true);
    // return $movies['Movies'];

    $cinemark = new YeremiCinemarkApi();
    $decode   = $cinemark->getMovies();
    if ($decode) {
        $movies = json_decode($decode, true);
        return $movies['Movies'];
    }
    return false;
}

get_header();

?>
<div class="container-fluid">
    <div class="wrap-content">
        <?php
        if (get_movies_cinemark()):

            foreach (get_movies_cinemark() as $movie) {

                $trailerUrl        = null;
                $trailerDesktopImg = null;
                $trailerMobileImg  = null;
                $trailerTabletImg  = null;
                $posterDesktop     = null;
                $posterMobile      = null;
                $posterTablet      = null;

                foreach ($movie['Assets'] as $asset) {
                    if ($asset['Type'] === 10) {
                        $trailerDesktopImg = $asset['url'];
                    }
                    if ($asset['Type'] === 20) {
                        $trailerMobileImg = $asset['url'];
                    }
                    if ($asset['Type'] === 30) {
                        $trailerTabletImg = $asset['url'];
                    }
                    if ($asset['Type'] === 40) {
                        $trailerUrl = $asset['url'];
                    }
                    if ($asset['Type'] === 50) {
                        $posterDesktop = $asset['url'];
                    }
                    if ($asset['Type'] === 60) {
                        $posterMobile = $asset['url'];
                    }
                    if ($asset['Type'] === 70) {
                        $posterTablet = $asset['url'];
                    }
                }

                $genres = [];
                foreach ($movie['Genres'] as $genre) {
                    $genres[] = $genre['Description'];
                }
                ?>
                <div class="row box-info border border-grey border-radius5"
                     id="<?php echo $movie['MovieCode']; ?>">
                    <div class="col s6 m3 l3">
                        <?php if (cinemark_poster_exists($posterDesktop)) { ?>
                            <img src="<?php echo $posterDesktop; ?>" class="cartaz-img">
                        <?php } ?>
                    </div>
                    <div class="col s6 m7 l5">
                        <h2 class="font-strong"><?php echo $movie['LocalTitle']; ?></h2>

                        <h5>
                            De <?php echo date('d/m/Y', strtotime($movie['CineWeekStartDate'])); ?>
                            à <?php echo date('d/m/Y', strtotime($movie['CineWeekEndDate'])); ?>
                        </h5>

                        <p>
                            <?php echo $movie['RunTime']; ?> min. <br/>

                            <?php echo $movie['Rating']; ?> <br/>

                            <?php echo $movie['Director']; ?> <br/>

                            <?php if ($genres) {
                                _e(implode(', ', $genres));
                            } ?>
                        </p>

                        <?php if ($trailerUrl) { ?>

                            Url do Trailer: <?php echo $trailerUrl; ?>

                        <?php } ?>
                    </div>

                    <div class="col s4">
                        <div class="row">
                            <?php
                            foreach ($movie['Dates'] as $show) {

                                echo date('d/m/Y', strtotime($show['Date'])) . '<br />';

                                foreach ($show['ShowTimes'] as $times) {
                                    //echo $show['id']. '<br />';
                                    //echo $show['cm']. '<br />';
                                    //echo $show['tht']. '<br />';
                                    //echo $show['thtName']. '<br />';
                                    //echo $show['aud'] . '<br />';
                                    //echo $show['xd'] . '<br />';
                                    //echo $show['prime'] . '<br />';
                                    //echo $show['dbox'] . '<br />';
                                    //echo $show['d3d'] . '<br />';
                                    //echo $show['deb'] . '<br />';
                                    //echo $show['psl'] . '<br />';
                                    echo $times['time'] . ' | ';
                                    //echo $show['loc'] . '<br />';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }//end foreach
        else:
            echo 'Não há filmes para mostrar';
        endif;
        ?>
    </div>
</div>

<style>
    .badge.custom {
        display: inline-block;
        float: none;
        font-size: 11px;
        color: #fff;
        margin-left: 4px;
        height: 20px;
        min-width: 2rem;
        border-radius: 2px;
    }
</style>
<?php get_footer() ?>
