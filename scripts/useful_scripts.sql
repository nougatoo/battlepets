-- Gets all the global market avg value for each pet
select market_value_pets.species_id, avg(market_value)/10000, pets.name
from market_value_pets
inner join pets on market_value_pets.species_id = pets.species_id
group by market_value_pets.species_id, name
order by avg(market_value) asc