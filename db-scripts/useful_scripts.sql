-- Gets all the global market avg value for each pet
select market_value_pets.species_id, avg(market_value)/10000, pets.name
from market_value_pets
inner join pets on market_value_pets.species_id = pets.species_id
group by market_value_pets.species_id, name
order by avg(market_value) asc

-- gets the price difference between the global avg and the current min buy for pets for a set of servers
select a.species_id, a.name, b.realm, a.gblavg, b.minbuy, a.gblavg-b.minbuy as diff from (select market_value_pets.species_id, avg(market_value)/10000 as gblavg, pets.name
from market_value_pets
inner join pets on market_value_pets.species_id = pets.species_id
group by market_value_pets.species_id, name
) a
inner join (
select min(buyout)/10000 as minbuy, species_id, realm from auctions_daily_pet group by species_id, realm) b
on a.species_id = b.species_id
where realm in ('cenarion-circle', 'wyrmrest-accord')
order by diff desc