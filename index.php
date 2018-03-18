<?php

include("util.php");

$conn = dbConnect();

customLog("index", "logging function test");


$sql = "select a.species_id, a.name, b.realm, a.gblavg, b.minbuy, a.gblavg-b.minbuy as diff from (select market_value_pets.species_id, avg(market_value)/10000 as gblavg, pets.name
from market_value_pets
inner join pets on market_value_pets.species_id = pets.species_id
group by market_value_pets.species_id, name
) a
inner join (
select min(buyout)/10000 as minbuy, species_id, realm from auctions_daily_pet group by species_id, realm) b
on a.species_id = b.species_id
where realm in ('cenarion-circle', 'wyrmrest-accord')
order by diff desc";


$globalAvgs = $conn->query($sql);

if ($globalAvgs->num_rows > 0) {
	// output data of each row
	while($row = $globalAvgs->fetch_assoc()) {
		echo ($row['name']."\t".$row['realm']."\t".$row['gblavg']."\t".$row['diff']."<br/>");
	}
} else {
	echo "0 results";
}

?>