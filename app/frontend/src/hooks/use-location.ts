'use client';

import { useState, useEffect, useCallback } from 'react';
import { getDepartments, getCities, getNeighborhoods } from '@/lib/api/location';
import type { Department, City, Neighborhood } from '@/types/location';

/**
 * Hook for cascading geolocation selects
 * Manages: departments → cities → neighborhoods
 * 
 * Features:
 * - Auto-fetch departments on mount
 * - Auto-fetch cities when department changes
 * - Auto-fetch neighborhoods when city changes
 * - Auto-reset cascading selections
 * - Loading/error states for each level
 */
interface UseLocationReturn {
  departments: Department[];
  cities: City[];
  neighborhoods: Neighborhood[];
  loading: {
    departments: boolean;
    cities: boolean;
    neighborhoods: boolean;
  };
  error: {
    departments: string | null;
    cities: string | null;
    neighborhoods: string | null;
  };
  selectedDept: number | null;
  selectedCity: number | null;
  selectedNeighborhood: number | null;
  setSelectedDept: (id: number | null) => void;
  setSelectedCity: (id: number | null) => void;
  setSelectedNeighborhood: (id: number | null) => void;
  reset: () => void;
}

export function useLocation(): UseLocationReturn {
  // ============================================
  // State: Data
  // ============================================
  const [departments, setDepartments] = useState<Department[]>([]);
  const [cities, setCities] = useState<City[]>([]);
  const [neighborhoods, setNeighborhoods] = useState<Neighborhood[]>([]);

  // ============================================
  // State: Selections
  // ============================================
  const [selectedDept, setSelectedDeptState] = useState<number | null>(null);
  const [selectedCity, setSelectedCityState] = useState<number | null>(null);
  const [selectedNeighborhood, setSelectedNeighborhoodState] = useState<number | null>(null);

  // ============================================
  // State: Loading & Errors
  // ============================================
  const [loading, setLoading] = useState({
    departments: false,
    cities: false,
    neighborhoods: false,
  });

  const [error, setError] = useState({
    departments: null as string | null,
    cities: null as string | null,
    neighborhoods: null as string | null,
  });

  // ============================================
  // Effect: Fetch Departments on Mount
  // ============================================
  useEffect(() => {
    const fetchDepartments = async () => {
      setLoading((prev) => ({ ...prev, departments: true }));
      setError((prev) => ({ ...prev, departments: null }));
      try {
        const response = await getDepartments({ per_page: 2000 });
        setDepartments(response.data);
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'Error al cargar departamentos';
        setError((prev) => ({ ...prev, departments: errorMessage }));
      } finally {
        setLoading((prev) => ({ ...prev, departments: false }));
      }
    };

    fetchDepartments();
  }, []);

  // ============================================
  // Effect: Fetch Cities when Department Changes
  // ============================================
  useEffect(() => {
    if (!selectedDept) {
      setCities([]);
      setSelectedCityState(null);
      setNeighborhoods([]);
      setSelectedNeighborhoodState(null);
      return;
    }

    const fetchCities = async () => {
      setLoading((prev) => ({ ...prev, cities: true }));
      setError((prev) => ({ ...prev, cities: null }));
      try {
        const response = await getCities({ department_id: selectedDept, per_page: 2000 });
        setCities(response.data);
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'Error al cargar ciudades';
        setError((prev) => ({ ...prev, cities: errorMessage }));
      } finally {
        setLoading((prev) => ({ ...prev, cities: false }));
      }
    };

    fetchCities();
  }, [selectedDept]);

  // ============================================
  // Effect: Fetch Neighborhoods when City Changes
  // ============================================
  useEffect(() => {
    if (!selectedCity) {
      setNeighborhoods([]);
      setSelectedNeighborhoodState(null);
      return;
    }

    const fetchNeighborhoods = async () => {
      setLoading((prev) => ({ ...prev, neighborhoods: true }));
      setError((prev) => ({ ...prev, neighborhoods: null }));
      try {
        const response = await getNeighborhoods({ city_id: selectedCity, per_page: 2000 });
        setNeighborhoods(response.data);
      } catch (err) {
        const errorMessage = err instanceof Error ? err.message : 'Error al cargar barrios';
        setError((prev) => ({ ...prev, neighborhoods: errorMessage }));
      } finally {
        setLoading((prev) => ({ ...prev, neighborhoods: false }));
      }
    };

    fetchNeighborhoods();
  }, [selectedCity]);

  // ============================================
  // Handlers: Selection Changes with Cascading
  // ============================================

  const setSelectedDept = useCallback((id: number | null) => {
    setSelectedDeptState(id);
    // Reset cascading: when dept changes, reset city and neighborhood
    setSelectedCityState(null);
    setSelectedNeighborhoodState(null);
  }, []);

  const setSelectedCity = useCallback((id: number | null) => {
    setSelectedCityState(id);
    // Reset cascading: when city changes, reset neighborhood
    setSelectedNeighborhoodState(null);
  }, []);

  const setSelectedNeighborhood = useCallback((id: number | null) => {
    setSelectedNeighborhoodState(id);
  }, []);

  const reset = useCallback(() => {
    setSelectedDeptState(null);
    setSelectedCityState(null);
    setSelectedNeighborhoodState(null);
    setDepartments([]);
    setCities([]);
    setNeighborhoods([]);
  }, []);

  // ============================================
  // Return
  // ============================================
  return {
    departments,
    cities,
    neighborhoods,
    loading,
    error,
    selectedDept,
    selectedCity,
    selectedNeighborhood,
    setSelectedDept,
    setSelectedCity,
    setSelectedNeighborhood,
    reset,
  };
}
